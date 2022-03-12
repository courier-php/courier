<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

use Courier\Message\CommandInterface;
use Courier\Message\Envelope;
use Courier\Message\EventInterface;
use Courier\Serializer\SerializerInterface;

interface ProducerInterceptorInterface {
  public function beforeEventSerialize(EventInterface &$event): ProducerInterceptorResultEnum;

  public function afterEventSerialize(
    Envelope &$envelope,
    EventInterface $event,
    SerializerInterface $serializer
  ): ProducerInterceptorResultEnum;

  public function beforeSendEvent(Envelope &$envelope): ProducerInterceptorResultEnum;

  public function afterSendEvent(Envelope $envelope): void;

  public function beforeCommandSerialize(CommandInterface &$command): ProducerInterceptorResultEnum;

  public function afterCommandSerialize(
    Envelope &$envelope,
    CommandInterface $command,
    SerializerInterface $serializer
  ): ProducerInterceptorResultEnum;

  public function beforeSendCommand(Envelope &$envelope): ProducerInterceptorResultEnum;

  public function afterSendCommand(Envelope $envelope): void;
}
