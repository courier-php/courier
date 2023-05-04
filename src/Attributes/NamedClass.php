<?php
declare(strict_types = 1);

namespace Courier\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS)]
class NamedClass {
  private string $name;

  public function __construct(string $name) {
    if (trim($name) === '') {
      throw new InvalidArgumentException('$name argument cannot be empty');
    }

    $this->name = $name;
  }

  public function getName(): string {
    return $this->name;
  }
}
