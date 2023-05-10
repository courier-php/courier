<?php
declare(strict_types = 1);

namespace Courier\Traits;

use ReflectionClass;

trait PublicMessagePayloadTrait {
  /**
   * @return array<string, mixed>
   */
  public function getPayload(): array {
    $payload = [];

    $reflectedClass = new ReflectionClass(static::class);
    foreach ($reflectedClass->getProperties() as $reflectedProperty) {
      if ($reflectedProperty->isPublic()) {
        if ($reflectedProperty->isInitialized($this) === false) {
          continue;
        }

        $payload[$reflectedProperty->getName()] = $reflectedProperty->getValue($this);
      }
    }

    return $payload;
  }
}
