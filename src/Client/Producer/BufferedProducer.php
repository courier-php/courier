<?php
declare(strict_types = 1);

namespace Courier\Client\Producer;

use Courier\Interceptor\ProducerInterceptorInterface;
use Courier\Message\CommandInterface;
use Courier\Message\EventInterface;
use Courier\Middleware\MiddlewareInterface;

final class BufferedProducer implements ProducerInterface {
  private ProducerInterface $producer;
  private int $bufferSize;
  /**
   * @var \Courier\Message\EventInterface[]
   */
  private array $eventBuffer = [];
  /**
   * @var \Courier\Message\CommandInterface[]
   */
  private array $commandBuffer = [];

  public function __construct(ProducerInterface $producer, int $bufferSize = 10) {
    $this->producer   = $producer;
    $this->bufferSize = $bufferSize;
  }

  public function __destruct() {
    // ensure all events are sent before leaving
    while (count($this->eventBuffer)) {
      $this->producer->sendEvent(array_shift($this->eventBuffer));
    }

    // ensure all commands are sent before leaving
    while (count($this->commandBuffer)) {
      $this->producer->sendCommand(array_shift($this->commandBuffer));
    }
  }

  public function addMiddleware(MiddlewareInterface $middleware): static {
    $this->producer->addMiddleware($middleware);

    return $this;
  }

  public function setInterceptor(?ProducerInterceptorInterface $producerInterceptor = null): static {
    $this->producer->setInterceptor($producerInterceptor);

    return $this;
  }

  public function getInterceptor(): ?ProducerInterceptorInterface {
    return $this->producer->getInterceptor();
  }

  public function sendEvent(EventInterface $event): void {
    $this->eventBuffer[] = $event;
    if (count($this->eventBuffer) < $this->bufferSize) {
      return;
    }

    while (count($this->eventBuffer)) {
      $this->producer->sendEvent(array_shift($this->eventBuffer));
    }
  }

  public function sendCommand(CommandInterface $command): void {
    $this->commandBuffer[] = $command;
    if (count($this->commandBuffer) < $this->bufferSize) {
      return;
    }

    while (count($this->commandBuffer)) {
      $this->producer->sendCommand(array_shift($this->commandBuffer));
    }
  }
}
