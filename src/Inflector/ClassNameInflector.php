<?php
declare(strict_types = 1);

namespace Courier\Inflector;

use Courier\Message\MessageInterface;
use Courier\Processor\ProcessorInterface;

class ClassNameInflector implements InflectorInterface {
  public function resolve(MessageInterface $message, ProcessorInterface $processor): string {
    $class = $message::class;
    if (strpos($class, '\\') !== false) {
      $class = substr($class, strrpos($class, '\\') + 1);
    }

    return lcfirst($class);
  }
}
