<?php
declare(strict_types = 1);

namespace Courier\Clients;

use Closure;
use Courier\Contracts\Bus\BusInterface;
use Courier\Contracts\Clients\CollectorInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Transports\TransportInterface;
use Courier\Exceptions\ProcessorException;
use Throwable;

class ObservableCollector implements CollectorInterface {
  private BusInterface $bus;
  private TransportInterface $transport;
  /**
   * @var array<string, Closure[]>
   */
  private array $observers = [];
  private bool $stop;

  private function notify(string $event, string $queueName = null, MessageInterface $message = null): void {
    if (isset($this->observers[$event]) === false) {
      return;
    }

    foreach ($this->observers[$event] as $observer) {
      $observer->call($this, $queueName, $message);
    }
  }

  public function __construct(BusInterface $bus, TransportInterface $transport) {
    $this->bus = $bus;
    $this->transport = $transport;
  }

  /**
   * @param callable(string, ?MessageInterface): void $observer
   */
  public function addObserver(string $event, callable $observer): self {
    if (isset($this->observers[$event]) === false) {
      $this->observers[$event] = [];
    }

    $this->observers[$event][] = Closure::fromCallable($observer);

    return $this;
  }

  public function stop(): void {
    $this->stop = true;
  }

  public function collect(string ...$queueNames): void {
    $this->stop = false;
    foreach ($queueNames as $queueName) {
      if ($this->stop === true) {
        $this->notify('stop', $queueName);

        break;
      }

      $this->notify('start', $queueName);
      $message = $this->transport->collect($queueName);
      if ($message === null) {
        $this->notify('empty-queue', $queueName);

        continue;
      }

      $message->setProperty('delivery', 'incoming');
      $this->notify('received', $queueName, $message);

      try {
        $this->bus->handle($message);

        $this->transport->accept($message);
        $this->notify('accepted', $queueName, $message);
      } catch (ProcessorException $exception) {
        $this->transport->reject($message, true);
        $this->notify('requeued', $queueName, $message);
      } catch (Throwable $exception) {
        $this->transport->reject($message);
        $this->notify('rejected', $queueName, $message);

        throw $exception;
      } finally {
        $this->notify('done', $queueName, $message);
      }
    }
  }
}
