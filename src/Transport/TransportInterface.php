<?php
declare(strict_types = 1);

namespace Courier\Transport;

use Courier\Message\Envelope;

interface TransportInterface {
  /**
   * Transport initialization
   *
   * @throws \Courier\Exception\TransportException
   */
  public function init(): void;
  /**
   * Binds a queue to a route
   *
   * @throws \Courier\Exception\TransportException
   */
  public function bindQueue(string $queue, string $route): void;

  /**
   * Unbinds a route to a queue
   *
   * @throws \Courier\Exception\TransportException
   */
  public function unbindQueue(string $queue, string $route): void;

  /**
   * Get the number of pending messages in queue
   *
   * @throws \Courier\Exception\TransportException
   */
  public function pending(string $queue): int;

  /**
   * Send envelope to queue
   *
   * @throws \Courier\Exception\TransportException
   */
  public function send(string $queue, Envelope $envelope): void;

  /**
   * Get envelope from queue
   *
   * @throws \Courier\Exception\TransportException
   */
  public function recv(string $queue): ?Envelope;

  /**
   * Accept envelope (ACK)
   *
   * @throws \Courier\Exception\TransportException
   */
  public function accept(Envelope $envelope): void;

  /**
   * Reject/Requeue envelope (NACK)
   *
   * @throws \Courier\Exception\TransportException
   */
  public function reject(Envelope $envelope, bool $requeue = false): void;
}
