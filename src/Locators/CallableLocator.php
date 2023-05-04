<?php
declare(strict_types = 1);

namespace Courier\Locators;

use Courier\Contracts\Locators\LocatorInterface;
use Courier\Contracts\Processors\HandlerInterface;
use Courier\Contracts\Processors\ListenerInterface;
use InvalidArgumentException;

class CallableLocator implements LocatorInterface {
  /**
   * @var array<string, callable(): HandlerInterface|ListenerInterface>
   */
  private array $instances = [];

  /**
   * @param callable(): HandlerInterface|ListenerInterface $callable
   */
  public function addInstance(string $class, callable $callable): self {
    $this->instances[$class] = $callable;

    return $this;
  }
  public function instanceFor(
    string $class
  ): HandlerInterface | ListenerInterface {
    if (isset($this->instances[$class])) {
      return $this->instances[$class]();
    }

    throw new InvalidArgumentException("Factory callable for \"{$class}\" was not found in CallableLocator");
  }
}
