<?php
declare(strict_types = 1);

namespace Courier\Middleware;

use Courier\Message\Envelope;

interface MiddlewareInterface {
  public function __invoke(Envelope $envelope, callable $next): Envelope;
}
