<?php
declare(strict_types = 1);

namespace Courier\Router;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends \Ramsey\Collection\AbstractCollection<\Courier\Router\Route>
 */
final class RouteCollection extends AbstractCollection {
  public function getType(): string {
    return Route::class;
  }
}
