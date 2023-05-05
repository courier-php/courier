<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Inflectors\InflectorInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use Courier\Contracts\Providers\ProviderInterface;
use Courier\Contracts\Resolvers\ResolverInterface;
use Courier\Contracts\Transports\TransportInterface;

class AsyncMiddleware implements MiddlewareInterface {
  private InflectorInterface $inflector;
  private ResolverInterface $resolver;
  private ProviderInterface $provider;
  private TransportInterface $transport;

  private bool $isRegistered = false;

  public function __construct(
    InflectorInterface $inflector,
    ResolverInterface $resolver,
    ProviderInterface $provider,
    TransportInterface $transport
  ) {
    $this->inflector = $inflector;
    $this->resolver = $resolver;
    $this->provider = $provider;
    $this->transport = $transport;
  }

  public function register(): self {
    foreach ($this->provider as $class) {
      $queueName = $this->inflector->resolve($class);
      foreach ($this->resolver->resolve($class) as $entry) {
        $this->transport->addRoute(
          $this->inflector->resolve($entry['subjectClass']),
          $queueName
        );
      }
    }

    $this->isRegistered = true;

    return $this;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($this->isRegistered === false) {
      $this->register();
    }

    if ($message->hasProperty('delivery') && $message->getProperty('delivery') === 'outgoing') {
      $routingKey = $this->inflector->resolve($message::class);

      $this->transport->publish($message, $routingKey);

      $message->setProperty('routingKey', $routingKey);
    }

    $next($message);
  }
}
