<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

use Courier\Message\CommandInterface;
use Courier\Message\Envelope;
use Courier\Message\EventInterface;
use Courier\Message\MessageInterface;
use Courier\Processor\Handler\HandlerInterface;
use Courier\Processor\Handler\HandlerResultEnum;
use Courier\Processor\Listener\ListenerInterface;
use Courier\Serializer\SerializerInterface;

abstract class AbstractConsumerInterceptor implements ConsumerInterceptorInterface {
  public function beforeConsume(): void {}

  public function afterConsume(): void {}

  public function beforeReceive(
    string &$queueName
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function afterReceive(
    string $queueName,
    Envelope &$envelope
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function beforeUnserialize(
    Envelope &$envelope,
    SerializerInterface $serializer
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function afterUnserialize(
    Envelope $envelope,
    MessageInterface &$message
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function beforeListener(
    EventInterface $event,
    ListenerInterface $listener,
    string $methodName
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function afterListener(
    EventInterface $event,
    ListenerInterface $listener,
    string $methodName
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function beforeHandler(
    CommandInterface $command,
    HandlerInterface $handler,
    string $methodName
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }

  public function afterHandler(
    CommandInterface $command,
    HandlerInterface $handler,
    string $methodName,
    HandlerResultEnum $result
  ): ConsumerInterceptorResultEnum {
    return ConsumerInterceptorResultEnum::PASS;
  }
}
