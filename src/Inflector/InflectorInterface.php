<?php
declare(strict_types = 1);

namespace Courier\Inflector;

use Courier\Message\MessageInterface;
use Courier\Processor\ProcessorInterface;

interface InflectorInterface {
  public function resolve(MessageInterface $message, ProcessorInterface $processor): string;
}
