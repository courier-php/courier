<?php
declare(strict_types = 1);

namespace Courier\Router;

use InvalidArgumentException;

final class SimpleRouter implements RouterInterface {
  private RouteCollection $routeCollection;
  private array $routableMessages = [];

  private function findRoute(callable $filter, string $query): Route {
    $routes = $this->routeCollection->filter($filter);

    if ($routes->isEmpty()) {
      throw new InvalidArgumentException(
        sprintf(
          'Route "%s" not found',
          $query
        )
      );
    }

    return $routes->first();
  }

  public function __construct() {
    $this->routeCollection = new RouteCollection();
  }

  public function getRoutes(): RouteCollection {
    return $this->routeCollection;
  }

  public function findRouteByQueueName(string $queueName): Route {
    static $cache = [];
    if (isset($cache[$queueName]) === false) {
      $cache[$queueName] = $this->findRoute(
        static function (Route $route) use ($queueName): bool {
          return $route->getQueueName() === $queueName;
        },
        $queueName
      );
    }

    return $cache[$queueName];
  }

  public function findRouteByRoutingKey(string $routingKey): Route {
    static $cache = [];
    if (isset($cache[$routingKey]) === false) {
      $cache[$routingKey] = $this->findRoute(
        static function (Route $route) use ($routingKey): bool {
          return $route->getRoutingKey() === $routingKey;
        },
        $routingKey
      );
    }

    return $cache[$routingKey];
  }

  public function findRouteByProcessorClass(string $processorClass): Route {
    static $cache = [];
    if (isset($cache[$processorClass]) === false) {
      $cache[$processorClass] = $this->findRoute(
        static function (Route $route) use ($processorClass): bool {
          return $route->getProcessorClass() === $processorClass;
        },
        $processorClass
      );
    }

    return $cache[$processorClass];
  }

  public function findRouteByMessageClass(string $messageClass): Route {
    static $cache = [];
    if (isset($cache[$messageClass]) === false) {
      $cache[$messageClass] = $this->findRoute(
        static function (Route $route) use ($messageClass): bool {
          return $route->getMessageClass() === $messageClass;
        },
        $messageClass
      );
    }

    return $cache[$messageClass];
  }

  public function findRouteByName(string $routeName): Route {
    static $cache = [];
    if (isset($cache[$routeName]) === false) {
      $cache[$routeName] = $this->findRoute(
        static function (Route $route) use ($routeName): bool {
          return $route->getRouteName() === $routeName;
        },
        $routeName
      );
    }

    return $cache[$routeName];
  }

  public function addRoute(
    string $messageClass,
    string $processorClass,
    string $routeName = null
  ): self {
    if (class_exists($messageClass) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Invalid message class "%s"',
          $messageClass
        )
      );
    }

    if (class_exists($processorClass) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Invalid processor class "%s"',
          $processorClass
        )
      );
    }

    $this->routeCollection->add(
      Route::create(
        $processorClass,
        $messageClass,
        $routeName ?? bin2hex(random_bytes(5))
      )
    );

    if (in_array($messageClass, $this->routableMessages, true) === false) {
      $this->routableMessages[] = $messageClass;
    }

    return $this;
  }

  public function isRoutable(string $messageClass): bool {
    return in_array($messageClass, $this->routableMessages, true);
  }
}
