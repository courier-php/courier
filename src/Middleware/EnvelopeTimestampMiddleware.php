<?php
declare(strict_types = 1);

namespace Courier\Middleware;

use Courier\Message\Envelope;
use DateTimeImmutable;

final class EnvelopeTimestampMiddleware implements MiddlewareInterface {
  public function __invoke(Envelope $envelope, callable $next): Envelope {
    if ($envelope->getTimestamp() === null) {
      $envelope = $envelope->withTimestamp(new DateTimeImmutable());
    }

    return $next($envelope);
  }
}
