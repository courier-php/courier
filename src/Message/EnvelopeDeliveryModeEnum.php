<?php
declare(strict_types = 1);

namespace Courier\Message;

enum EnvelopeDeliveryModeEnum: int {
  case TRANSIENT = 1;
  case PERSISTENT = 2;
}
