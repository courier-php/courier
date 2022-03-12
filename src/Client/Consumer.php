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
use Courier\Message\EventInterface;
use Courier\Processor\Handler\HandlerResultEnum;
use Courier\Router\Route;
use Courier\Serializer\PhpSerializer;
use Courier\Serializer\SerializerInterface;
use InvalidArgumentException;

final class Consumer extends AbstractClient {
  private LocatorInterface $locator;
  private InflectorInterface $inflector;
  private ?ConsumerInterceptorInterface $consumerInterceptor = null;

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

  public function setInterceptor(?ConsumerInterceptorInterface $consumerInterceptor = null): self {
    $this->consumerInterceptor = $consumerInterceptor;

    return $this;
  }

  public function getInterceptor(): ?ConsumerInterceptorInterface {
    return $this->consumerInterceptor;
  }

  public function consume(string $routeName, int $messageCount = 1): int {
    $routes = $this->bus->getRouter()->getRoutes()->filter(
      function (Route $route) use ($routeName): bool {
        return $route->getRouteName() === $routeName;
      }
    );

    if ($routes->isEmpty()) {
      throw new InvalidArgumentException('Route not found');
    }

    return $this->consumeRoute($routes->first(), $messageCount);
  }

  public function consumeAll(int $messageCount = 1): int {
    $consumedMessages = 0;
    foreach ($this->bus->getRouter()->getRoutes() as $route) {
      $consumedMessages += $this->consumeRoute($route, $messageCount);
    }

    return $consumedMessages;
  }

  public function consumeRoute(route $route, int $messageCount = 1): int {
    if ($messageCount < 0) {
      throw new InvalidArgumentException('$messageCount must be a positive integer');
    }

    $transport = $this->bus->getTransport();
    $queueName = $route->getQueueName();
    if ($transport->pending($queueName) === 0) {
      return 0;
    }

    if ($this->consumerInterceptor !== null) {
      $this->consumerInterceptor->beforeConsume();
    }

    $consumedMessages = 0;
    while ($consumedMessages < $messageCount) {
      if ($this->consumerInterceptor !== null) {
        switch ($this->consumerInterceptor->beforeReceive($queueName)) {
          case ConsumerInterceptorResultEnum::Pass:
            // do nothing, keep running
            break;
          case ConsumerInterceptorResultEnum::Skip:
            continue 2;
          case ConsumerInterceptorResultEnum::Stop:
            break 2;
        }
      }

      $envelope = $transport->recv($queueName);
      if ($envelope === null) {
        return $consumedMessages;
      }

      if ($this->consumerInterceptor !== null) {
        switch ($this->consumerInterceptor->afterReceive($queueName, $envelope)) {
          case ConsumerInterceptorResultEnum::Pass:
            // do nothing, keep running
            break;
          case ConsumerInterceptorResultEnum::Skip:
            continue 2;
          case ConsumerInterceptorResultEnum::Stop:
            break 2;
        }
      }

      $envelope = $this->processMiddlewareStack($envelope);

      if ($this->consumerInterceptor !== null) {
        switch ($this->consumerInterceptor->beforeUnserialize($envelope, $this->serializer)) {
          case ConsumerInterceptorResultEnum::Pass:
            // do nothing, keep running
            break;
          case ConsumerInterceptorResultEnum::Skip:
            continue 2;
          case ConsumerInterceptorResultEnum::Stop:
            break 2;
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
          case ConsumerInterceptorResultEnum::Pass:
            // do nothing, keep running
            break;
          case ConsumerInterceptorResultEnum::Skip:
            continue 2;
          case ConsumerInterceptorResultEnum::Stop:
            break 2;
        }
      }

      if ($message instanceof EventInterface) {
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
              case ConsumerInterceptorResultEnum::Pass:
                // do nothing, keep running
                break;
              case ConsumerInterceptorResultEnum::Skip:
                continue 2;
              case ConsumerInterceptorResultEnum::Stop:
                break 2;
            }
          }

          $listener->{$methodName}($message, $envelope->getAttributes());
          $consumedMessages++;

          if ($this->consumerInterceptor !== null) {
            switch ($this->consumerInterceptor->afterListener($message, $listener, $methodName)) {
              case ConsumerInterceptorResultEnum::Pass:
                // do nothing, keep running
                break;
              case ConsumerInterceptorResultEnum::Skip:
                continue 2;
              case ConsumerInterceptorResultEnum::Stop:
                break 2;
            }
          }
        }

        $transport->accept($envelope);

        continue;
      }

      if ($message instanceof CommandInterface) {
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
            case ConsumerInterceptorResultEnum::Pass:
              // do nothing, keep running
              break;
            case ConsumerInterceptorResultEnum::Skip:
              continue 2;
            case ConsumerInterceptorResultEnum::Stop:
              break 2;
          }
        }

        $result = $handler->{$methodName}($message, $envelope->getAttributes());
        $consumedMessages++;

        if ($this->consumerInterceptor !== null) {
          switch ($this->consumerInterceptor->afterHandler($message, $handler, $methodName, $result)) {
            case ConsumerInterceptorResultEnum::Pass:
              // do nothing, keep running
              break;
            case ConsumerInterceptorResultEnum::Skip:
              continue 2;
            case ConsumerInterceptorResultEnum::Stop:
              break 2;
          }
        }

        switch ($result) {
          case HandlerResultEnum::Accept:
            $transport->accept($envelope);
            break;

          case HandlerResultEnum::Reject:
            $transport->reject($envelope, false);
            break;

          case HandlerResultEnum::Requeue:
            $transport->reject($envelope, true);
            break;
        }

        continue;
      }

      throw new MessageTypeException(
        sprintf(
          'Invalid message class "%s"',
          $message::class
        )
      );
    }

    if ($this->consumerInterceptor !== null) {
      $this->consumerInterceptor->afterConsume();
    }

    return $consumedMessages;
  }
}
