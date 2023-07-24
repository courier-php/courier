<?php
declare(strict_types = 1);

namespace Courier\Messages;

use Courier\Contracts\Messages\MessageInterface;
use RuntimeException;

abstract class AbstractMessage implements MessageInterface {
  /**
   * @var array<string, mixed>
   */
  protected array $attributes = [];
  /**
   * @var array<string, mixed>
   */
  protected array $properties = [];

  public function getAttribute(string $name): mixed {
    return $this->attributes[$name] ?? null;
  }

  public function getAttributes(): array {
    return $this->attributes;
  }

  public function hasAttribute(string $name): bool {
    return isset($this->attributes[$name]);
  }

  public function setAttribute(string $name, mixed $value): MessageInterface {
    $this->attributes[$name] = $value;

    return $this;
  }

  public function setAttributes(array $attributes): MessageInterface {
    foreach ($attributes as $name => $value) {
      $this->setAttribute($name, $value);
    }

    return $this;
  }

  public function unsetAttribute(string $name): MessageInterface {
    unset($this->attributes[$name]);

    return $this;
  }

  public function getProperty(string $name, mixed $default = null): mixed {
    return $this->properties[$name] ?? $default;
  }

  public function getProperties(): array {
    return $this->properties;
  }

  public function hasProperty(string $name): bool {
    return isset($this->properties[$name]);
  }

  public function setProperty(string $name, mixed $value): MessageInterface {
    if ($this->hasProperty($name) === true) {
      throw new RuntimeException("Cannot overwrite property \"{$name}\"");
    }

    $this->properties[$name] = $value;

    return $this;
  }

  public function setProperties(array $properties): MessageInterface {
    foreach ($properties as $name => $value) {
      $this->setProperty($name, $value);
    }

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function __serialize(): array {
    return ['attributes' => $this->attributes];
  }

  /**
   * @param array<string, mixed> $data
   */
  public function __unserialize(array $data): void {
    $this->attributes = $data['attributes'];
  }
}
