<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Attributes\AsyncMessage;
use Courier\Contracts\Locators\LocatorInterface;
use Courier\Contracts\Messages\CommandInterface;
use Courier\Contracts\Messages\EventInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use Courier\Contracts\Resolvers\ResolverInterface;
use InvalidArgumentException;
use ReflectionClass;

class SyncMiddleware implements MiddlewareInterface {
  private ResolverInterface $processorResolver;
  private LocatorInterface $locator;
  private array $handlers = [];
  private array $listeners = [];

  public function __construct(
    ResolverInterface $processorResolver,
    LocatorInterface $locator
  ) {
    $this->processorResolver = $processorResolver;
    $this->locator = $locator;
  }

  public function register(string $class): self {
    if (class_exists($class) === false) {
      throw new InvalidArgumentException("Class \"{$class}\" was not found");
    }

    // throw new InvalidArgumentException(
    //   "Class \"{$class}\" does not implement neither HandlerInterface nor ListenerInterface"
    // );

    foreach ($this->processorResolver->resolve($class) as $entry) {
      switch ($entry['subjectType']) {
        case CommandInterface::class:
          $this->handlers[$entry['subjectClass']] = [$class, $entry['methodName']];
          break;
        case EventInterface::class:
          $this->listeners[$entry['subjectClass']][] = [$class, $entry['methodName']];
          break;
        default:
          throw new InvalidArgumentException(
            "Unknown subject type \"{$entry['subjectType']}\" for target class \"{$class}\""
          );
      }
    }

    return $this;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($message->hasAttribute('delivery') && $message->getAttribute('delivery') === 'outgoing') {
      $reflectedClass = new ReflectionClass($message);
      $attributes = $reflectedClass->getAttributes(AsyncMessage::class);
      if (count($attributes) > 0) {
        // this middleware cannot handle outgoing messages marked as #[AsyncMessage]
        $next($message);

        return;
      }
    }

    $implements = class_implements($message);
    if ($message instanceof CommandInterface) {
      if (isset($this->handlers[$message::class]) === true) {
        // can be handled locally
        [$class, $method] = $this->handlers[$message::class];
        $handler = $this->locator->instanceFor($class);
        $handler->$method($message);
        $message->setAttribute('handler', $class);
      }
    }

    if ($message instanceof EventInterface) {
      if (isset($this->listeners[$message::class]) === true) {
        // can be handled locally
        foreach ($this->listeners[$message::class] as $classMap) {
          [$class, $method] = $classMap;
          $listener = $this->locator->instanceFor($class);
          $listener->$method($message);
        }

        $message->setAttribute('listenerCount', count($this->listeners[$message::class]));
      }
    }

    $next($message);
  }
}
