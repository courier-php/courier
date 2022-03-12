<?php
declare(strict_types = 1);

namespace Courier\Interceptor;

enum ConsumerInterceptorResultEnum: string {
  case Pass = 'pass';
  case Skip = 'skip';
  case Stop = 'stop';
}
