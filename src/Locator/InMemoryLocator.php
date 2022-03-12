<?php
declare(strict_types = 1);

namespace Courier\Locator;

use Courier\Exception\LocatorException;
use Courier\Processor\ProcessorInterface;

/**
 * Fetch processor instances from an in-memory collection.
 */
final class InMemoryLocator implements LocatorInterface {
  /**
   * @var array<string, \Courier\Processor\ProcessorInterface>
   */
  private array $instances = [];

  public function __construct(array $instances = []) {
    foreach ($instances as $class => $instance) {
      $this->addInstance($class, $instance);
    }
  }

  public function addInstance(string $class, ProcessorInterface $instance): self {
    $this->instances[$class] = $instance;

    return $this;
  }

  public function getInstanceFor(string $class): ?ProcessorInterface {
    if (isset($this->instances[$class])) {
      return $this->instances[$class];
    }

    return null;
  }
}
