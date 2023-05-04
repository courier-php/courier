<?php
declare(strict_types = 1);

namespace Courier\Inflectors;

use Courier\Contracts\Inflectors\InflectorInterface;
use Courier\Traits\NameAwareTrait;
use InvalidArgumentException;

class TraitBasedInflector implements InflectorInterface {
  private ?InflectorInterface $fallback;

  public function __construct(InflectorInterface $fallback = null) {
    $this->fallback = $fallback;
  }

  public function resolve(string $class): string {
    $uses = class_uses($class);
    if (in_array(NameAwareTrait::class, $uses, true) === false) {
      if ($this->fallback === null) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" must have the NameAwareTrait trait to be inferred by TraitBasedInflector"
        );
      }

      return $this->fallback->resolve($class);
    }

    return $class::getName();
  }
}
