<?php
declare(strict_types = 1);

namespace Courier\Middlewares;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;
use Throwable;

class SingleMessageMiddleware implements MiddlewareInterface {
  /**
   * @var MessageInterface[]
   */
  private array $queue = [];
  private bool $isBusy = false;

  public function handle(MessageInterface $message, callable $next): void {
    $this->queue[] = $message;

    if ($this->isBusy === false) {
      $this->isBusy = true;

      while ($message = array_shift($this->queue)) {
        try {
          $next($message);
        } catch (Throwable $exception) {
          $this->isBusy = false;

          throw $exception;
        }
      }

      $this->isBusy = false;
    }
  }
}
