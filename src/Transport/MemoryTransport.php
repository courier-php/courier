<?php
declare(strict_types = 1);

namespace Courier\Transport;

use Courier\Message\Envelope;
use Courier\Processor\Handler\HandlerResultEnum;

final class MemoryTransport implements TransportInterface {
  private array $routingMap = [];
  private array $messageBucket = [];
  private array $subscribeList = [];

  public function init(): void {
    // no-op
  }

  public function getAttribute(string $name): mixed {
    // no-op
    return null;
  }

  public function setAttribute(string $name, mixed $value): static {
    // no-op
    return $this;
  }

  public function bindQueue(string $queueName, string $routingKey): void {
    // if this is an unknown queue, create a message bucket for it
    if (in_array($queueName, $this->messageBucket, true) === false) {
      $this->messageBucket[$queueName] = [];
    }

    // if the route does not exist, create it
    if (isset($this->routingMap[$routingKey]) === false) {
      $this->routingMap[$routingKey] = [];
    }

    // if the queue is not bond to this route, add it
    if (in_array($queueName, $this->routingMap[$routingKey], true) === false) {
      $this->routingMap[$routingKey][] = $queueName;
    }
  }

  public function unbindQueue(string $queueName, string $routingKey): void {
    // remove the message bucket for the route
    unset($this->messageBucket[$routingKey][$queueName]);

    // remove the route
    $index = array_search($queueName, $this->routingMap[$routingKey]);
    unset($this->routingMap[$routingKey][$index]);

  }

  public function pending(string $queueName): int {
    if (isset($this->messageBucket[$queueName]) === false) {
      return 0;
    }

    return count($this->messageBucket[$queueName]);
  }

  public function purge(string $queueName): void {
    if (isset($this->messageBucket[$queueName]) === true) {
      $this->messageBucket[$queueName] = [];
    }
  }

  public function subscribe(string $queueName): void {
    // if queue is already subscribed, leave
    if (in_array($queueName, $this->subscribeList, true) === true) {
      return;
    }

    // add queue to subscribe list
    $this->subscribeList[] = $queueName;
  }

  public function consume(string $queueName, callable $consumer, callable $stop): int {
    $this->subscribeList[] = $queueName;
    $subscribeList = array_unique($this->subscribeList);

    $accepted = 0;
    $rejected = 0;
    $requeued = 0;
    $consumed = 0;

    while (true) {
      foreach ($subscribeList as $queueName) {
        $envelope = array_shift($this->messageBucket[$queueName]);
        if ($envelope !== null) {
          $envelope = unserialize($envelope);
        }

        if ($envelope instanceof Envelope) {
          switch ($consumer($envelope, $queueName)) {
            case HandlerResultEnum::ACCEPT:
              $accepted++;
              break;
            case HandlerResultEnum::REJECT:
              // array_shift has already taken the envelope off of this $messageBucket
              $rejected++;
              break;
            case HandlerResultEnum::REQUEUE:
              $this->messageBucket[$queueName][] = $envelope;
              $requeued++;
              break;
          }

          $consumed++;
        }

        if ($stop($accepted, $rejected, $requeued, $consumed) === true) {
          $this->subscribeList = [];

          break;
        }
      }
    }

    return $consumed;
  }

  public function send(string $routingKey, Envelope $envelope): void {
    if (isset($this->routingMap[$routingKey]) === false) {
      return;
    }

    $serializedEnvelope = serialize($envelope);
    foreach ($this->routingMap[$routingKey] as $queueName) {
      $this->messageBucket[$queueName][] = $serializedEnvelope;
    }
  }

  public function accept(Envelope $envelope): void {
    // no-op
  }

  public function reject(Envelope $envelope, bool $requeue = false): void {
    // no-op
  }
}
