<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Attributes\UniqueMessage;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use ReflectionClass;

class UniqueMessageMiddleware implements MiddlewareInterface {
  /**
   * @var array<string, int>
   */
  private array $control = [];

  public function handle(MessageInterface $message, callable $next): void {
    $reflectedClass = new ReflectionClass($message);
    $attributes = $reflectedClass->getAttributes(UniqueMessage::class);
    if ($attributes === []) {
      $next($message);

      return;
    }

    $reflectedAttribute = $attributes[0]->newInstance();
    if ($message->hasAttribute($reflectedAttribute->getAttribute()) === false) {
      $next($message);

      return;
    }

    $messageId = $message->getAttribute($reflectedAttribute->getAttribute());
    $expiresAt = $reflectedAttribute->getExpiresAt();
    if (isset($this->control[$messageId]) === true && $this->control[$messageId] >= time()) {
      return;
    }

    $this->control[$messageId] = $expiresAt;

    $next($message);
  }
}
