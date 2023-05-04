<?php
declare(strict_types = 1);

namespace Courier\Clients;

use Courier\Contracts\Bus\BusInterface;
use Courier\Contracts\Clients\CollectorInterface;
use Courier\Contracts\Transports\TransportInterface;
use Exception;

class Collector implements CollectorInterface {
  private BusInterface $bus;
  private TransportInterface $transport;
  private bool $exit = false;

  public function __construct(BusInterface $bus, TransportInterface $transport) {
    $this->bus = $bus;
    $this->transport = $transport;
  }

  public function stop(): void {
    $this->exit = true;
  }

  public function collect(string ...$queueNames): void {
    while ($this->exit === false) {
      foreach ($queueNames as $queueName) {
        $message = $this->transport->collect($queueName);
        if ($message === null) {
          continue;
        }

        $message->setProperty('delivery', 'incoming');

        try {
          $this->bus->handle($message);

          $this->transport->accept($message);
        } catch (Exception $exception) {
          $this->transport->reject($message);

          throw $exception;
        }

        usleep(100);
      }

      usleep(10);
    }
  }
}
