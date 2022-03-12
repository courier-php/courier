<?php
declare(strict_types = 1);

namespace Courier\Inflector;

use Courier\Message\MessageInterface;
use Courier\Processor\ProcessorInterface;

final class HandleClassNameInflector extends ClassNameInflector {
  public function resolve(MessageInterface $message, ProcessorInterface $processor): string {
    return 'handle' . ucfirst(parent::resolve($message, $processor));
  }
}
