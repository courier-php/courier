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

interface ConsumerInterceptorInterface {
  public function beforeConsume(): void;

  public function afterConsume(): void;

  public function beforeReceive(string &$queueName): ConsumerInterceptorResultEnum;

  public function afterReceive(string $queueName, Envelope &$envelope): ConsumerInterceptorResultEnum;

  public function beforeUnserialize(
    Envelope &$envelope,
    SerializerInterface $serializer
  ): ConsumerInterceptorResultEnum;

  public function afterUnserialize(Envelope $envelope, MessageInterface &$message): ConsumerInterceptorResultEnum;

  public function beforeListener(
    EventInterface $event,
    ListenerInterface $listener,
    string $methodName
  ): ConsumerInterceptorResultEnum;

  public function afterListener(
    EventInterface $event,
    ListenerInterface $listener,
    string $methodName
  ): ConsumerInterceptorResultEnum;

  public function beforeHandler(
    CommandInterface $command,
    HandlerInterface $handler,
    string $methodName
  ): ConsumerInterceptorResultEnum;

  public function afterHandler(
    CommandInterface $command, 
    HandlerInterface $handler, 
    string $methodName, 
    HandlerResultEnum $result
  ): ConsumerInterceptorResultEnum;
}
