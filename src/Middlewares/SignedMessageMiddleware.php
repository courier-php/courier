<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use InvalidArgumentException;

class SignedMessageMiddleware implements MiddlewareInterface {
  private string $key;

  public function __construct(string $key) {
    if (trim($key) === '') {
      throw new InvalidArgumentException('$key argument must not be empty');
    }

    $this->key = $key;
  }

  public function handle(MessageInterface $message, callable $next): void {
    if ($message->hasAttribute('signature') === false || empty($message->getAttribute('signature')) === true) {
      $payload = $message->getPayload();
      ksort($payload);
      $data = http_build_query($payload);
      $signature = sprintf(
        'sha256:%s',
        hash_hmac('sha256', $data, $this->key)
      );

      $message->setAttribute('signature', $signature);
    }

    $next($message);
  }
}
