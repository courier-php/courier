<?php
declare(strict_types = 1);

namespace Courier\Router;

interface RouterInterface {
  public function getRoutes(): RouteCollection;

  public function addRoute(string $messageClass, string $processorClass, string $routeName = null): self;

  public function isRoutable(string $messageClass): bool;
}
