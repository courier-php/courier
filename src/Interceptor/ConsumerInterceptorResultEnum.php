<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

enum ConsumerInterceptorResultEnum {
  case PASS;
  case SKIP;
  case STOP;
}
