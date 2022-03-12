<?php
declare(strict_types = 1);

namespace Courier\Message;

enum EnvelopeDeliveryModeEnum: int {
  case Transient = 1;
  case Persistent = 2;
}
