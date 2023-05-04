<?php
declare(strict_types = 1);

namespace Courier\Resolvers;

use Courier\Attributes\HandlerFor;
use Courier\Attributes\ListenerFor;
use Courier\Contracts\Messages\CommandInterface;
use Courier\Contracts\Messages\EventInterface;
use Courier\Contracts\Processors\HandlerInterface;
use Courier\Contracts\Processors\ListenerInterface;
use Courier\Contracts\Resolvers\ResolverInterface;
use LogicException;
use ReflectionClass;

class AttributeBasedResolver implements ResolverInterface {
  public function resolve(string $class): iterable {
    $implements = class_implements($class);
    $reflectedClass = new ReflectionClass($class);

    if (in_array(HandlerInterface::class, $implements, true) === true) {
      $attributes = $reflectedClass->getAttributes(HandlerFor::class);
      if (count($attributes) > 0 && method_exists($class, '__invoke') === false) {
        throw new LogicException(
          "Cannot add attribute \"HandlerFor\" to class \"{$class}\" without implementing the \"__invoke\" method"
        );
      }

      foreach ($attributes as $reflectedAttribute) {
        // $class->__invoke()
        $attribute = $reflectedAttribute->newInstance();
        yield [
          'subjectClass' => $attribute->getCommandClass(),
          'subjectType' => CommandInterface::class,
          'methodName' => '__invoke'
        ];
      }

      foreach ($reflectedClass->getMethods() as $reflectedMethod) {
        $attributes = $reflectedMethod->getAttributes(HandlerFor::class);
        foreach ($attributes as $reflectedAttribute) {
          // $class->$method
          $attribute = $reflectedAttribute->newInstance();
          yield [
            'subjectClass' => $attribute->getCommandClass(),
            'subjectType' => CommandInterface::class,
            'methodName' => $reflectedMethod->name
          ];
        }
      }
    }

    if (in_array(ListenerInterface::class, $implements, true) === true) {
      $attributes = $reflectedClass->getAttributes(ListenerFor::class);
      if (count($attributes) > 0 && method_exists($class, '__invoke') === false) {
        throw new LogicException(
          "Cannot add attribute \"ListenerFor\" to class \"{$class}\" without implementing the \"__invoke\" method"
        );
      }

      foreach ($attributes as $reflectedAttribute) {
        // $class->__invoke()
        $attribute = $reflectedAttribute->newInstance();
        yield [
          'subjectClass' => $attribute->getEventClass(),
          'subjectType' => EventInterface::class,
          'methodName' => '__invoke'
        ];
      }

      foreach ($reflectedClass->getMethods() as $reflectedMethod) {
        $attributes = $reflectedMethod->getAttributes(ListenerFor::class);
        foreach ($attributes as $reflectedAttribute) {
          // $class->$method
          $attribute = $reflectedAttribute->newInstance();
          yield [
            'subjectClass' => $attribute->getEventClass(),
            'subjectType' => EventInterface::class,
            'methodName' => $reflectedMethod->name
          ];
        }
      }
    }
  }
}
