<?php
declare(strict_types = 1);

namespace Courier\Middleware;

use Courier\Message\Envelope;

final class EnvelopeIdMiddleware implements MiddlewareInterface {
  private int $length;

  public function __construct(int $length = 10) {
    // ensure that the min length is 4 bytes
    $this->length = max($length, 4);
  }

  public function __invoke(Envelope $envelope, callable $next): Envelope {
    if ($envelope->getMessageId() === '') {
      $envelope = $envelope->withMessageId(bin2hex(random_bytes($this->length)));
    }

    return $next($envelope);
  }
}
