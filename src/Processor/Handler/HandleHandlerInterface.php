<?php
declare(strict_types = 1);

namespace Courier\Processor\Handler;

use Courier\Message\CommandInterface;

interface HandleHandlerInterface extends HandlerInterface {
  public function handle(CommandInterface $command, array $attributes = []): HandlerResultEnum;
}
