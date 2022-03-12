<?php
declare(strict_types = 1);

namespace Courier\Client;

use Courier\Bus;
use Courier\Exception\HandlerException;
use Courier\Exception\ListenerException;
use Courier\Exception\LocatorException;
use Courier\Exception\MessageTypeException;
use Courier\Exception\UnserializeException;
use Courier\Inflector\InflectorInterface;
use Courier\Interceptor\ConsumerInterceptorInterface;
use Courier\Interceptor\ConsumerInterceptorResultEnum;
use Courier\Locator\LocatorInterface;
use Courier\Message\CommandInterface;
use Courier\Message\Envelope;
use Courier\Message\EventInterface;
use Courier\Processor\Handler\HandlerResultEnum;
use Courier\Router\Route;
use Courier\Router\RouteCollection;
use Courier\Serializer\PhpSerializer;
use Courier\Serializer\SerializerInterface;
use Courier\Transport\TransportResultEnum;
use InvalidArgumentException;

final class Consumer extends AbstractClient {
  private LocatorInterface $locator;
  private InflectorInterface $inflector;
  private ?ConsumerInterceptorInterface $consumerInterceptor = null;
  private bool $shouldStop = false;

  public function __construct(
    Bus $bus,
    InflectorInterface $inflector,
    LocatorInterface $locator,
    SerializerInterface $serializer = new PhpSerializer()
  ) {
    $this->inflector = $inflector;
    $this->locator   = $locator;

    parent::__construct($bus, $serializer);
  }

  public function getInflector(): InflectorInterface {
    return $this->inflector;
  }

  public function getLocator(): LocatorInterface {
    return $this->locator;
  }

  public function setInterceptor(ConsumerInterceptorInterface $consumerInterceptor): self {
    $this->consumerInterceptor = $consumerInterceptor;

    return $this;
  }

  public function getInterceptor(): ?ConsumerInterceptorInterface {
    return $this->consumerInterceptor;
  }

  public function stop(): void {
    $this->shouldStop = true;
  }

  public function shouldStop(): bool {
    return $this->shouldStop;
  }

  /**
   * Consume messages from $routeName until $predicate returns true.
   *
   * If $routeName is null, this method will consume *all* available routes.
   * If $predicate is null, this method will block and keep message consumption until the process
   * is stopped externally.
   *
   * @param callable $predicate function (int $accepted, int $rejected, int $requeued, int $consumed): bool
   *
   * Note: $consumed = $accepted + $rejected + $requeued
   */
  public function consume(string $routeName = null, callable $predicate = null): int {
    if ($routeName === null) {
      return $this->consumeRoutes($this->bus->getRouter()->getRoutes(), $predicate);
    }

    return $this->consumeRoute(
      $this->bus->getRouter()->findRouteByName($routeName),
      $predicate
    );
  }

  /**
   * Consume messages from $route until $predicate returns true.
   *
   * If $predicate is null, this method will block and keep message consumption until the process
   * is stopped externally.
   *
   * @param callable $predicate function (int $accepted, int $rejected, int $requeued, int $consumed): bool
   *
   * Note: $consumed = $accepted + $rejected + $requeued
   */
  public function consumeRoute(Route $route, callable $predicate = null): int {
    return $this->consumeRoutes(new RouteCollection([$route]), $predicate);
  }

  /**
   * Consume messages from all routes in $routes until $predicate returns true.
   *
   * If $predicate is null, this method will block and keep message consumption until the process
   * is stopped externally.
   *
   * @param callable $predicate function (int $accepted, int $rejected, int $requeued, int $consumed): bool
   *
   * Note: $consumed = $accepted + $rejected + $requeued
   */
  public function consumeRoutes(RouteCollection $routes, callable $predicate = null): int {
    $transport = $this->bus->getTransport();

    $routes = $routes->toArray();
    $lastRoute = array_pop($routes);
    foreach ($routes as $route) {
      $transport->subscribe($route->getQueueName());
    }

    if ($this->consumerInterceptor !== null) {
      $this->consumerInterceptor->beforeConsume();
    }

    $this->shouldStop = false;
    $consumedMessages = $transport->consume(
      $lastRoute->getQueueName(),
      function (
        Envelope $envelope,
        string $queueName
      ): TransportResultEnum {
        if ($this->consumerInterceptor !== null) {
          switch ($this->consumerInterceptor->afterReceive($queueName, $envelope)) {
            case ConsumerInterceptorResultEnum::PASS:
              // do nothing, keep running
              break;
            case ConsumerInterceptorResultEnum::SKIP:
              return TransportResultEnum::ACCEPT;
            case ConsumerInterceptorResultEnum::STOP:
              $this->stop();
              return TransportResultEnum::ACCEPT;
          }
        }

        $envelope = $this->processMiddlewareStack($envelope);

        if ($this->consumerInterceptor !== null) {
          switch ($this->consumerInterceptor->beforeUnserialize($envelope, $this->serializer)) {
            case ConsumerInterceptorResultEnum::PASS:
              // do nothing, keep running
              break;
            case ConsumerInterceptorResultEnum::SKIP:
              return TransportResultEnum::ACCEPT;
            case ConsumerInterceptorResultEnum::STOP:
              $this->stop();
              return TransportResultEnum::ACCEPT;
          }
        }

        if ($envelope->getContentEncoding() !== $this->serializer->getContentEncoding()) {
          throw new UnserializeException(
            sprintf(
              'Cannot decode "%s" using "%s"',
              $envelope->getContentEncoding(),
              ($this->serializer)::class
            )
          );
        }

        if ($envelope->getContentType() !== $this->serializer->getContentType()) {
          throw new UnserializeException(
            sprintf(
              'Cannot unserialize "%s" using "%s"',
              $envelope->getContentType(),
              ($this->serializer)::class
            )
          );
        }

        $body = $this->serializer->unserialize($envelope->getBody());
        $class = $envelope->getType();
        if (class_exists($class) === false) {
          throw new UnserializeException(
            sprintf(
              'Unknown class "%s"',
              $class
            )
          );
        }

        $message = new $class(...$body);

        if ($this->consumerInterceptor !== null) {
          switch ($this->consumerInterceptor->afterUnserialize($envelope, $message)) {
            case ConsumerInterceptorResultEnum::PASS:
              // do nothing, keep running
              break;
            case ConsumerInterceptorResultEnum::SKIP:
              return TransportResultEnum::ACCEPT;
            case ConsumerInterceptorResultEnum::STOP:
              $this->stop();
              return TransportResultEnum::ACCEPT;
          }
        }

        if ($message instanceof EventInterface) {
          $route = $this->bus->getRouter()->findRouteByMessageClass($class);
          $listener = $this->locator->getInstanceFor($route->getProcessorClass());
          if ($listener !== null) {
            $methodName = $this->inflector->resolve($message, $listener);
            if (is_callable([$listener, $methodName]) === false) {
              throw new ListenerException(
                sprintf(
                  'Method "%s" does not exist on listener "%s"',
                  $methodName,
                  $listener::class
                )
              );
            }

            if ($this->consumerInterceptor !== null) {
              switch ($this->consumerInterceptor->beforeListener($message, $listener, $methodName)) {
                case ConsumerInterceptorResultEnum::PASS:
                  // do nothing, keep running
                  break;
                case ConsumerInterceptorResultEnum::SKIP:
                  return TransportResultEnum::ACCEPT;
                case ConsumerInterceptorResultEnum::STOP:
                  $this->stop();
                  return TransportResultEnum::ACCEPT;
              }
            }

            $listener->{$methodName}($message, $envelope->getAttributes());

            if ($this->consumerInterceptor !== null) {
              switch ($this->consumerInterceptor->afterListener($message, $listener, $methodName)) {
                case ConsumerInterceptorResultEnum::PASS:
                  // do nothing, keep running
                  break;
                case ConsumerInterceptorResultEnum::SKIP:
                  return TransportResultEnum::ACCEPT;
                case ConsumerInterceptorResultEnum::STOP:
                  $this->stop();
                  return TransportResultEnum::ACCEPT;
              }
            }
          }

          return TransportResultEnum::ACCEPT;
        }

        if ($message instanceof CommandInterface) {
          $route = $this->bus->getRouter()->findRouteByMessageClass($class);
          $handler = $this->locator->getInstanceFor($route->getProcessorClass());
          if ($handler === null) {
            throw new LocatorException(
              sprintf(
                'Could not locate an instance of "%s"',
                $route->getProcessorClass()
              )
            );
          }

          $methodName = $this->inflector->resolve($message, $handler);
          if (is_callable([$handler, $methodName]) === false) {
            throw new HandlerException(
              sprintf(
                'Method "%s" does not exist on handler "%s"',
                $methodName,
                $handler::class
              )
            );
          }

          if ($this->consumerInterceptor !== null) {
            switch ($this->consumerInterceptor->beforeHandler($message, $handler, $methodName)) {
              case ConsumerInterceptorResultEnum::PASS:
                // do nothing, keep running
                break;
              case ConsumerInterceptorResultEnum::SKIP:
                return TransportResultEnum::ACCEPT;
              case ConsumerInterceptorResultEnum::STOP:
                $this->stop();
                return TransportResultEnum::ACCEPT;
            }
          }

          $result = $handler->{$methodName}($message, $envelope->getAttributes());

          if ($this->consumerInterceptor !== null) {
            switch ($this->consumerInterceptor->afterHandler($message, $handler, $methodName, $result)) {
              case ConsumerInterceptorResultEnum::PASS:
                // do nothing, keep running
                break;
              case ConsumerInterceptorResultEnum::SKIP:
                return match ($result) {
                  HandlerResultEnum::ACCEPT => TransportResultEnum::ACCEPT,
                  HandlerResultEnum::REJECT => TransportResultEnum::REJECT,
                  HandlerResultEnum::REQUEUE => TransportResultEnum::REQUEUE
                };
              case ConsumerInterceptorResultEnum::STOP:
                $this->stop();
                return match ($result) {
                  HandlerResultEnum::ACCEPT => TransportResultEnum::ACCEPT,
                  HandlerResultEnum::REJECT => TransportResultEnum::REJECT,
                  HandlerResultEnum::REQUEUE => TransportResultEnum::REQUEUE
                };
            }
          }

          return match ($result) {
            HandlerResultEnum::ACCEPT => TransportResultEnum::ACCEPT,
            HandlerResultEnum::REJECT => TransportResultEnum::REJECT,
            HandlerResultEnum::REQUEUE => TransportResultEnum::REQUEUE
          };
        }

        throw new MessageTypeException(
          sprintf(
            'Invalid message class "%s"',
            $message::class
          )
        );
      },
      function (int $accepted, int $rejected, int $requeued, int $consumed) use ($predicate) {
        if ($predicate !== null) {
          return $predicate($accepted, $rejected, $requeued, $consumed) || $this->shouldStop;
        }

        return $this->shouldStop;
      }
    );

    if ($this->consumerInterceptor !== null) {
      $this->consumerInterceptor->afterConsume();
    }

    return $consumedMessages;
  }
}
