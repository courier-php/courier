<?php
declare(strict_types = 1);

namespace Courier\Client;

use Courier\Bus;
use Courier\Message\Envelope;
use Courier\Middleware\MiddlewareInterface;
use Courier\Serializer\PhpSerializer;
use Courier\Serializer\SerializerInterface;

abstract class AbstractClient {
  protected Bus $bus;
  /**
   * @var \Courier\Middleware\MiddlewareInterface[]
   */
  protected array $middleware = [];
  protected SerializerInterface $serializer;
  
  protected function processMiddlewareStack(Envelope $envelope): Envelope {
    if (count($this->middleware) === 0) {
      return $envelope;
    }

    $chain = static function (Envelope $envelope): Envelope {
      return $envelope;
    };

    foreach ($this->middleware as $middleware) {
      $chain = static function (Envelope $envelope) use ($middleware, $chain): Envelope {
        return $middleware($envelope, $chain);
      };
    }

    return $chain($envelope);
  }

  public function __construct(
    Bus $bus,
    SerializerInterface $serializer = new PhpSerializer()
  ) {
    $this->bus        = $bus;
    $this->serializer = $serializer;
  }

  public function getBus(): Bus {
    return $this->bus;
  }

  public function getSerializer(): SerializerInterface {
    return $this->serializer;
  }

  public function addMiddleware(MiddlewareInterface $middleware): static {
    $this->middleware[] = $middleware;

    return $this;
  }
}
