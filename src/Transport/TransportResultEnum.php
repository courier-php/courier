<?php
declare(strict_types = 1);

namespace Courier\Transport;

enum TransportResultEnum {
  case ACCEPT;
  case REJECT;
  case REQUEUE;
}
