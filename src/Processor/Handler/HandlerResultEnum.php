<?php
declare(strict_types = 1);

namespace Courier\Processor\Handler;

enum HandlerResultEnum: string {
  case Accept = 'accept';
  case Reject = 'reject';
  case Requeue = 'requeue';
}
