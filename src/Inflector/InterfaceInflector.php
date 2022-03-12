<?php
declare(strict_types = 1);

namespace Courier\Inflector;

use Courier\Message\MessageInterface;
use Courier\Processor\Handler\HandleHandlerInterface;
use Courier\Processor\Handler\InvokeHandlerInterface;
use Courier\Processor\Listener\HandleListenerInterface;
use Courier\Processor\Listener\InvokeListenerInterface;
use Courier\Processor\ProcessorInterface;
use InvalidArgumentException;

class InterfaceInflector implements InflectorInterface {
  public function resolve(MessageInterface $message, ProcessorInterface $processor): string {
    if ($processor instanceof HandleHandlerInterface || $processor instanceof HandleListenerInterface) {
      return 'handle';
    }

    if ($processor instanceof InvokeHandlerInterface || $processor instanceof InvokeListenerInterface) {
      return '__invoke';
    }

    throw new InvalidArgumentException(
      sprintf(
        '"%s" does not implement any of the known processor interfaces',
        $processor::class
      )
    );
  }
}
