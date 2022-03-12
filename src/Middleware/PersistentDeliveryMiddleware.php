<?php
declare(strict_types = 1);

namespace Courier\Middleware;

use Courier\Message\Envelope;
use Courier\Message\EnvelopeDeliveryModeEnum;
use DateTimeImmutable;

final class PersistentDeliveryMiddleware implements MiddlewareInterface {
  public function __invoke(Envelope $envelope, callable $next): Envelope {
    return $next($envelope->withDeliveryMode(EnvelopeDeliveryModeEnum::PERSISTENT));
  }
}
