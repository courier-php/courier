<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use InvalidArgumentException;

class MessageIdMiddleware implements MiddlewareInterface {
  private int $length;

  public function __construct(int $length = 4) {
    if ($length < 1) {
      throw new InvalidArgumentException('$length argument must be a nonzero positive integer');
    }

    $this->length = $length;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($message->hasAttribute('id') === false || empty($message->getAttribute('id')) === true) {
      $message->setAttribute('id', bin2hex(random_bytes($this->length)));
    }

    $next($message);
  }
}
