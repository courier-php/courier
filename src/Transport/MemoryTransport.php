<?php
declare(strict_types = 1);

namespace Courier\Transport;

use Courier\Message\Envelope;
use InvalidArgumentException;

final class MemoryTransport implements TransportInterface {
  private array $queue = [];

  public function init(): void {}

  public function bindQueue(string $queue, string $route): void {}

  public function unbindQueue(string $queue, string $route): void {}

  public function pending(string $queue): int {
    if (isset($this->queue[$queue]) === false) {
      return 0;
    }

    return count($this->queue[$queue]);
  }

  /**
   * Send message to queue
   */
  public function send(string $queue, Envelope $envelope): void {
    if (isset($this->queue[$queue]) === false) {
      $this->queue[$queue] = [];
    }

    $index = bin2hex(random_bytes(5));
    $envelope = $envelope->withDeliveryTag(
      sprintf(
        '%s@%s',
        $index,
        $queue
      )
    );

    $this->queue[$queue][$index] = serialize($envelope);
  }

  /**
   * Get message from queue
   */
  public function recv(string $queue): ?Envelope {
    if (isset($this->queue[$queue]) === false || count($this->queue[$queue]) === 0) {
      return null;
    }

    $envelope = array_shift($this->queue[$queue]);

    return unserialize($envelope);
  }

  /**
   * Accept message (ACK)
   */
  public function accept(Envelope $envelope): void {
    $deliveryTag = $envelope->getDeliveryTag();

    $atPos = strpos($deliveryTag, '@');
    if ($atPos === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Invalid $deliveryTag string "%s"',
          $deliveryTag
        )
      );
    }

    $index = substr($deliveryTag, 0, $atPos);
    $queue = substr($deliveryTag, $atPos + 1);

    unset($this->queue[$queue][$index]);
  }

  /**
   * Reject/Requeue message (NACK)
   */
  public function reject(Envelope $envelope, bool $requeue = false): void {
    $deliveryTag = $envelope->getDeliveryTag();

    $atPos = strpos($deliveryTag, '@');
    if ($atPos === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Invalid $deliveryTag string "%s"',
          $deliveryTag
        )
      );
    }

    $index = substr($deliveryTag, 0, $atPos);
    $queue = substr($deliveryTag, $atPos + 1);

    if ($requeue === true) {
      $redeliveredMessage = unserialize($this->queue[$queue][$index]);
      $redeliveredMessage = $redeliveredMessage->withRedelivery(true);

      $this->queue[$queue][$index] = serialize($redeliveredMessage);

      return;
    }

    unset($this->queue[$queue][$index]);
  }
}
