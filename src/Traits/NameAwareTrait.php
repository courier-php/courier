<?php
declare(strict_types = 1);

namespace Courier\Traits;

trait NameAwareTrait {
  public static function getName(): string {
    return static::$name;
  }
}
