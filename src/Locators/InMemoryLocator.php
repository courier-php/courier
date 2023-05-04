<?php
declare(strict_types = 1);

namespace Courier\Locators;

use Courier\Contracts\Locators\LocatorInterface;
use Courier\Contracts\Processors\HandlerInterface;
use Courier\Contracts\Processors\ListenerInterface;
use InvalidArgumentException;

class InMemoryLocator implements LocatorInterface {
  /**
   * @var array<string, HandlerInterface|ListenerInterface>
   */
  private array $instances = [];

  public function addInstance(string $class, HandlerInterface|ListenerInterface $instance): self {
    $this->instances[$class] = $instance;

    return $this;
  }
  public function instanceFor(
    string $class
  ): HandlerInterface | ListenerInterface {
    if (isset($this->instances[$class])) {
      return $this->instances[$class];
    }

    throw new InvalidArgumentException("Instance for \"{$class}\" was not found in InMemoryLocator");
  }
}
