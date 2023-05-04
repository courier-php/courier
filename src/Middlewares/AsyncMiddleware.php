<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Inflectors\InflectorInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use Courier\Contracts\Resolvers\ResolverInterface;
use Courier\Contracts\Transports\TransportInterface;
use InvalidArgumentException;

class AsyncMiddleware implements MiddlewareInterface {
  private InflectorInterface $inflector;
  private ResolverInterface $resolver;
  private TransportInterface $transport;

  public function __construct(
    InflectorInterface $inflector,
    ResolverInterface $resolver,
    TransportInterface $transport
  ) {
    $this->inflector = $inflector;
    $this->resolver = $resolver;
    $this->transport = $transport;
  }

  public function register(string $class): self {
    if (class_exists($class) === false) {
      throw new InvalidArgumentException("Class \"{$class}\" was not found");
    }

    $queueName = $this->inflector->resolve($class);
    foreach ($this->resolver->resolve($class) as $entry) {
      $this->transport->addRoute(
        $this->inflector->resolve($entry['subjectClass']),
        $queueName
      );
    }

    return $this;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($message->hasProperty('delivery') && $message->getProperty('delivery') === 'outgoing') {
      $routingKey = $this->inflector->resolve($message::class);

      $this->transport->publish($message, $routingKey);

      $message->setProperty('routingKey', $routingKey);
    }

    $next($message);
  }
}
