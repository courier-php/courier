<?php
declare(strict_types = 1);

namespace Courier\Processor\Handler;

use Courier\Message\CommandInterface;

interface InvokeHandlerInterface extends HandlerInterface {
  public function __invoke(CommandInterface $command, array $attributes = []): HandlerResultEnum;
}
