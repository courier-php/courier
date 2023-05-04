<?php
declare(strict_types = 1);

namespace Courier\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HandlerFor {
  private string $commandClass;

  public function __construct(string $commandClass) {
    if (class_exists($commandClass) === false) {
      throw new InvalidArgumentException("Class \"{$commandClass}\" was not found");
    }

    $this->commandClass = $commandClass;
  }

  public function getCommandClass(): string {
    return $this->commandClass;
  }
}
