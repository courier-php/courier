<?php
declare(strict_types = 1);

namespace Courier;

use Courier\Command\CommandInterface;
use Courier\Command\HandlerInterface;
use Courier\Event\EventInterface;
use Courier\Event\ListenerInterface;
use Courier\Router\RouterInterface;
use Courier\Transport\TransportInterface;

final class Bus {
  private RouterInterface $router;
  private TransportInterface $transport;

  public function __construct(
    RouterInterface $router,
    TransportInterface $transport
  ) {
    $this->router    = $router;
    $this->transport = $transport;
  }

  public function getRouter(): RouterInterface {
    return $this->router;
  }

  public function setRouter(RouterInterface $router): self {
    $this->router = $router;

    return $this;
  }

  public function getTransport(): TransportInterface {
    return $this->transport;
  }

  public function setTransport(TransportInterface $transport): self {
    $this->transport = $transport;

    return $this;
  }

  public function bindRoutes(): void {
    $this->transport->init();
    foreach ($this->router->getRoutes() as $route) {
      $this->transport->bindQueue(
        $route->getQueueName(),
        $route->getRoutingKey()
      );
    }
  }

  public function unbindRoutes(): void {
    $this->transport->init();
    foreach ($this->router->getRoutes() as $route) {
      $this->transport->unbindQueue(
        $route->getQueueName(),
        $route->getRoutingKey()
      );
    }
  }
}
