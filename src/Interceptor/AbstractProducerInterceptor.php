<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

use Courier\Message\CommandInterface;
use Courier\Message\Envelope;
use Courier\Message\EventInterface;
use Courier\Serializer\SerializerInterface;

abstract class AbstractProducerInterceptor implements ProducerInterceptorInterface {
  public function beforeEventSerialize(
    EventInterface &$event
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function afterEventSerialize(
    Envelope &$envelope,
    EventInterface $event,
    SerializerInterface $serializer
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function beforeSendEvent(
    Envelope &$envelope
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function afterSendEvent(
    Envelope $envelope
  ): void {}

  public function beforeCommandSerialize(
    CommandInterface &$command
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function afterCommandSerialize(
    Envelope &$envelope,
    CommandInterface $command,
    SerializerInterface $serializer
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function beforeSendCommand(
    Envelope &$envelope
  ): ProducerInterceptorResultEnum {
    return ProducerInterceptorResultEnum::PASS;
  }

  public function afterSendCommand(
    Envelope $envelope
  ): void {}
}
