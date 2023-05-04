<?php
declare(strict_types = 1);

namespace Courier\Resolvers;

use Courier\Contracts\Resolvers\ResolverInterface;

class TraitBasedResolver implements ResolverInterface {
  public function resolve(string $class): iterable {
  }
}
