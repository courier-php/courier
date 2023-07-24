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

  /**
   * @return array<string, mixed>
   */
  public function __serialize(): array {
    return array_merge(
      parent::__serialize(),
      $this->getPayload()
    );
  }

  /**
   * @param array<string, mixed> $data
   */
  public function __unserialize(array $data): void {
    parent::__unserialize($data);

    $reflectedClass = new ReflectionClass(static::class);
    foreach ($reflectedClass->getProperties() as $reflectedProperty) {
      if ($reflectedProperty->isPublic() === false) {
        continue;
      }

      $propertyName = $reflectedProperty->getName();
      if (isset($data[$propertyName]) === false) {
        continue;
      }

      $this->$propertyName = $data[$propertyName];
    }
  }
}
