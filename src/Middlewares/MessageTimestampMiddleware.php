<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use DateTimeImmutable;
use DateTimeInterface;

class MessageTimestampMiddleware implements MiddlewareInterface {
  public function handle(MessageInterface $message, callable $next): void {
    if ($message->hasAttribute('timestamp') === false || empty($message->getAttribute('timestamp')) === true) {
      $message->setAttribute('timestamp', (new DateTimeImmutable)->format(DateTimeInterface::ATOM));
    }

    $next($message);
  }
}
