<?php
declare(strict_types = 1);

namespace Courier\Inflectors;

use Courier\Attributes\NamedClass;
use Courier\Contracts\Inflectors\InflectorInterface;
use InvalidArgumentException;
use ReflectionClass;

class AttributeBasedInflector implements InflectorInterface {
  private ?InflectorInterface $fallback;

  public function __construct(InflectorInterface $fallback = null) {
    $this->fallback = $fallback;
  }

  public function resolve(string $class): string {
    $reflectedClass = new ReflectionClass($class);
    $attributes = $reflectedClass->getAttributes(NamedClass::class);
    if (count($attributes) === 0) {
      if ($this->fallback === null) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" must have the NamedClass attribute to be inferred by AttributeBasedInflector"
        );
      }

      return $this->fallback->resolve($class);
    }

    $attribute = $attributes[0]->newInstance();

    return $attribute->getName();
  }
}
