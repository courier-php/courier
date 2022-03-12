<?php
declare(strict_types = 1);

namespace Courier\Processor\Listener;

use Courier\Message\EventInterface;

interface HandleListenerInterface extends ListenerInterface {
  public function handle(EventInterface $event, array $attributes = []): void;
}
