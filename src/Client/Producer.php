<?php
declare(strict_types = 1);

namespace Courier\Client;

use Courier\Interceptor\ProducerInterceptorInterface;
use Courier\Interceptor\ProducerInterceptorResultEnum;
use Courier\Message\CommandInterface;
use Courier\Message\Envelope;
use Courier\Message\EnvelopeDeliveryModeEnum;
use Courier\Message\EventInterface;
use Courier\Router\Route;
use LogicException;

final class Producer extends AbstractClient {
  private ?ProducerInterceptorInterface $producerInterceptor = null;

  public function setInterceptor(ProducerInterceptorInterface $producerInterceptor): static {
    $this->producerInterceptor = $producerInterceptor;

    return $this;
  }

  public function getInterceptor(): ?ProducerInterceptorInterface {
    return $this->producerInterceptor;
  }

  public function sendEvent(EventInterface $event): void {
    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->beforeEventSerialize($event)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $envelope = new Envelope(
      body: $this->serializer->serialize($event->toArray()),
      contentEncoding: $this->serializer->getContentEncoding(),
      contentType: $this->serializer->getContentType(),
      type: $event::class
    );

    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->afterEventSerialize($envelope, $event, $this->serializer)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $envelope = $this->processMiddlewareStack($envelope);

    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->beforeSendEvent($envelope)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $this->bus->getTransport()->send(
      Route::routingKey($event::class),
      $envelope
    );

    if ($this->producerInterceptor !== null) {
      $this->producerInterceptor->afterSendEvent($envelope);
    }
  }

  public function sendCommand(CommandInterface $command): void {
    if ($this->bus->getRouter()->isRoutable($command::class) === false) {
      throw new LogicException(
        sprintf(
          'There are no registered handlers for command "%s"',
          $command::class
        )
      );
    }

    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->beforeCommandSerialize($command)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $envelope = new Envelope(
      body: $this->serializer->serialize($command->toArray()),
      contentEncoding: $this->serializer->getContentEncoding(),
      contentType: $this->serializer->getContentType(),
      type: $command::class
    );

    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->afterCommandSerialize($envelope, $command, $this->serializer)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $envelope = $this->processMiddlewareStack($envelope);

    if ($this->producerInterceptor !== null) {
      switch ($this->producerInterceptor->beforeSendCommand($envelope)) {
        case ProducerInterceptorResultEnum::PASS:
          // do nothing, keep running
          break;
        case ProducerInterceptorResultEnum::STOP:
          return;
      }
    }

    $this->bus->getTransport()->send(
      Route::routingKey($command::class),
      $envelope
    );

    if ($this->producerInterceptor !== null) {
      $this->producerInterceptor->afterSendCommand($envelope);
    }
  }
}
