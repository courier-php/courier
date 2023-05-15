<?php
declare(strict_types = 1);

namespace Courier\Clients;

use Courier\Contracts\Bus\BusInterface;
use Courier\Contracts\Clients\CollectorInterface;
use Courier\Contracts\Transports\TransportInterface;
use Courier\Exceptions\ProcessorException;
use Throwable;

class Collector implements CollectorInterface {
  private BusInterface $bus;
  private TransportInterface $transport;
  private bool $stop;

  public function __construct(BusInterface $bus, TransportInterface $transport) {
    $this->bus = $bus;
    $this->transport = $transport;
  }

  public function stop(): void {
    $this->stop = true;
  }

  public function collect(string ...$queueNames): void {
    $this->stop = false;
    foreach ($queueNames as $queueName) {
      if ($this->stop === true) {
        break;
      }

      $message = $this->transport->collect($queueName);
      if ($message === null) {
        continue;
      }

      $message->setProperty('delivery', 'incoming');

      try {
        $this->bus->handle($message);

        $this->transport->accept($message);
      } catch (ProcessorException $exception) {
        $this->transport->reject($message, true);

        throw $exception;
      } catch (Throwable $exception) {
        $this->transport->reject($message);

        throw $exception;
      }

      usleep(100);
    }
  }
}
