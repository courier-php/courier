<?php
declare(strict_types = 1);

namespace Courier\Locator;

use Courier\Processor\ProcessorInterface;

/**
 * Service locator for listener and handler objects
 */
interface LocatorInterface {
  public function getInstanceFor(string $class): ?ProcessorInterface;
}
