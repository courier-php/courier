<?php
declare(strict_types = 1);

namespace Courier\Providers;

use Courier\Contracts\Processors\HandlerInterface;
use Courier\Contracts\Processors\ListenerInterface;
use Courier\Contracts\Providers\ProviderInterface;
use InvalidArgumentException;

class ArrayBasedProvider implements ProviderInterface {
  /**
   * @var string[]
   */
  private array $array;
  private int $count;
  private int $index;

  public function __construct(string ...$classes) {
    foreach ($classes as $class) {
      if (class_exists($class) === false) {
        throw new InvalidArgumentException("Class \"{$class}\" was not found");
      }

      $implements = class_implements($class);
      if (
        in_array(HandlerInterface::class, $implements, true) === false &&
        in_array(ListenerInterface::class, $implements, true) === false
      ) {
        throw new InvalidArgumentException(
          "Class \"{$class}\" does not implement neither \"HandlerInterface\" nor \"ListenerInterface\""
        );
      }

      $this->array[] = $class;
    }

    $this->count = count($this->array);
    $this->index = 0;
  }

  public function count(): int {
    return $this->count;
  }

  public function current(): string {
    return $this->array[$this->index];
  }

  public function key(): int {
    return $this->index;
  }

  public function next(): void {
    $this->index++;
  }

  public function rewind(): void {
    $this->index = 0;
  }

  public function valid(): bool {
    return isset($this->array[$this->index]) === true;
  }
}
