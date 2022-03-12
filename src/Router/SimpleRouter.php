<?php
declare(strict_types = 1);

namespace Courier\Router;

use InvalidArgumentException;

final class SimpleRouter implements RouterInterface {
  private RouteCollection $routeCollection;
  private array $routableMessages = [];

  public function __construct() {
    $this->routeCollection = new RouteCollection();
  }

  public function getRoutes(): RouteCollection {
    return $this->routeCollection;
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
