<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Attributes\AsyncMessage;
use Courier\Contracts\Locators\LocatorInterface;
use Courier\Contracts\Messages\CommandInterface;
use Courier\Contracts\Messages\EventInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use Courier\Contracts\Providers\ProviderInterface;
use Courier\Contracts\Resolvers\ResolverInterface;
use InvalidArgumentException;
use ReflectionClass;

class SyncMiddleware implements MiddlewareInterface {
  private ResolverInterface $resolver;
  private LocatorInterface $locator;
  private ProviderInterface $provider;
  private array $handlers = [];
  private array $listeners = [];

  private bool $isRegistered = false;

  public function __construct(
    ResolverInterface $resolver,
    LocatorInterface $locator,
    ProviderInterface $provider
  ) {
    $this->resolver = $resolver;
    $this->locator = $locator;
    $this->provider = $provider;
  }

  public function register(): self {
    foreach ($this->provider as $class) {
      foreach ($this->resolver->resolve($class) as $entry) {
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
    }

    $this->isRegistered = true;

    return $this;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($this->isRegistered === false) {
      $this->register();
    }

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
