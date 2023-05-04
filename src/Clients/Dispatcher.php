<?php
declare(strict_types = 1);

namespace Courier\Clients;

use Courier\Contracts\Bus\BusInterface;
use Courier\Contracts\Clients\DispatcherInterface;
use Courier\Contracts\Messages\MessageInterface;

class Dispatcher implements DispatcherInterface {
  private BusInterface $bus;

  public function __construct(BusInterface $bus) {
    $this->bus = $bus;
  }

  public function dispatch(MessageInterface $message): void {
    $message->setProperty('delivery', 'outgoing');

    $this->bus->handle($message);
  }
}
