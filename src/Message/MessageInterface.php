<?php
declare(strict_types = 1);

namespace Courier\Message;

interface MessageInterface {
  public function toArray(): array;
}
