<?php
declare(strict_types = 1);

namespace Courier\Client;

use Courier\Interceptor\ProducerInterceptorInterface;
use Courier\Message\CommandInterface;
use Courier\Message\EventInterface;
use Courier\Middleware\MiddlewareInterface;

final class BatchProducer {
  private Producer $producer;
  private int $batchSize;
  /**
   * @var \Courier\Message\EventInterface[]
   */
  private array $eventBatch = [];
  /**
   * @var \Courier\Message\CommandInterface[]
   */
  private array $commandBatch = [];

  public function __construct(Producer $producer, int $batchSize = 10) {
    $this->producer  = $producer;
    $this->batchSize = $batchSize;
  }

  public function __destruct() {
    // ensure all events are sent before leaving
    while (count($this->eventBatch)) {
      $this->producer->sendEvent(array_shift($this->eventBatch));
    }

    // ensure all commands are sent before leaving
    while (count($this->commandBatch)) {
      $this->producer->sendCommand(array_shift($this->commandBatch));
    }
  }

  public function sendEvent(EventInterface $event): void {
    $this->eventBatch[] = $event;
    if (count($this->eventBatch) < $this->batchSize) {
      return;
    }

    while (count($this->eventBatch)) {
      $this->producer->sendEvent(array_shift($this->eventBatch));
    }
  }

  public function sendCommand(CommandInterface $command): void {
    $this->commandBatch[] = $command;
    if (count($this->commandBatch) < $this->batchSize) {
      return;
    }

    while (count($this->commandBatch)) {
      $this->producer->sendCommand(array_shift($this->commandBatch));
    }
  }
}
