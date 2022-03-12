<?php
declare(strict_types = 1);

namespace Courier\Processor\Listener;

use Courier\Message\EventInterface;

interface InvokeListenerInterface extends ListenerInterface {
  public function __invoke(EventInterface $event, array $attributes = []): void;
}
