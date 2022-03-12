<?php
declare(strict_types = 1);

namespace Courier\Processor\Handler;

enum HandlerResultEnum {
  case ACCEPT;
  case REJECT;
  case REQUEUE;
}
