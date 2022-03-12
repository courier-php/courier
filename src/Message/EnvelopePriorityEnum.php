<?php
declare(strict_types = 1);

namespace Courier\Message;

enum EnvelopePriorityEnum: int {
  case VeryLow = 1;
  case Low = 2;
  case Normal = 3;
  case High = 4;
  case VeryHigh = 5;
}
