<?php
declare(strict_types = 1);

namespace Courier\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ListenerFor {
  private string $eventClass;

  public function __construct(string $eventClass) {
    if (class_exists($eventClass) === false) {
      throw new InvalidArgumentException("Class \"{$eventClass}\" was not found");
    }

    $this->eventClass = $eventClass;
  }

  public function getEventClass(): string {
    return $this->eventClass;
  }
}
