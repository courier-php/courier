<?php
declare(strict_types = 1);

namespace Courier;

use Courier\Contracts\Bus\BusInterface;
use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Middlewares\MiddlewareInterface;

class Bus implements BusInterface {
  /**
   * @var MiddlewareInterface[]
   */
  private array $middleware = [];

  private function buildMiddlewareChain(): callable {
    $chainFunc = static function (MessageInterface $message): void {
    };

    foreach ($this->middleware as $middleware) {
      $chainFunc = static function (MessageInterface $message) use ($middleware, $chainFunc): void {
        $middleware->handle($message, $chainFunc);
      };
    }

    return $chainFunc;
  }

  public function pushMiddleware(MiddlewareInterface $middleware): self {
    $this->middleware[] = $middleware;

    return $this;
  }

  public function handle(MessageInterface $message): void {
    $middlewareChain = $this->buildMiddlewareChain();
    $middlewareChain($message);
  }
}
