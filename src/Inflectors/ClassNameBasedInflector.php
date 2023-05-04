<?php
declare(strict_types = 1);

namespace Courier\Inflectors;

use Courier\Contracts\Inflectors\InflectorInterface;
use Courier\Contracts\Messages\CommandInterface;
use Courier\Contracts\Messages\EventInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Processors\HandlerInterface;
use Courier\Contracts\Processors\ListenerInterface;
use Courier\Contracts\Processors\ProcessorInterface;
use InvalidArgumentException;

class ClassNameBasedInflector implements InflectorInterface {
  private ?InflectorInterface $fallback;

  public function __construct(InflectorInterface $fallback = null) {
    $this->fallback = $fallback;
  }

  public function resolve(string $class): string {
    $prefix = '';
    $implements = class_implements($class);
    if (in_array(CommandInterface::class, $implements, true) === true) {
      $prefix = 'courier.command:';
    }

    if (in_array(EventInterface::class, $implements, true) === true) {
      $prefix = 'courier.event:';
    }

    if (in_array(HandlerInterface::class, $implements, true) === true) {
      $prefix = 'courier.handler:';
    }

    if (in_array(ListenerInterface::class, $implements, true) === true) {
      $prefix = 'courier.listener:';
    }

    if ($prefix === '') {
      if (in_array(MessageInterface::class, $implements, true) === true) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" must implement either \"CommandInterface\" or \"EventInterface\""
        );
      }

      if (in_array(ProcessorInterface::class, $implements, true) === true) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" must implement either \"HandlerInterface\" or \"ListenerInterface\""
        );
      }

      if ($this->fallback === null) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" name cannot be inferred by ClassNameBasedInflector"
        );
      }

      return $this->fallback->resolve($class);
    }

    $className = array_slice(
      explode(
        '\\',
        $class
      ),
      -1,
      1
    );

    return $prefix . lcfirst($className[0]);
  }
}
