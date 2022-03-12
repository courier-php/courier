<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

enum ProducerInterceptorResultEnum: string {
  case Pass = 'pass';
  case Stop = 'stop';
}
