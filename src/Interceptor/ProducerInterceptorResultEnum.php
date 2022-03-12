<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

enum ProducerInterceptorResultEnum {
  case PASS;
  case STOP;
}
