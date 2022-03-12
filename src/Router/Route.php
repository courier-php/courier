<?php
declare(strict_types = 1);

namespace Courier\Router;

final class Route {
  private string $queueName;
  private string $routingKey;
  private string $processorClass;
  private string $messageClass;
  private string $routeName;

  public static function queueName(string $processorClass): string {
    static $nameMap = [];
    if (isset($nameMap[$processorClass]) === false) {
      $pieces = explode('\\', $processorClass);
      $nameMap[$processorClass] = sprintf(
        'courier.queue:%s',
        lcfirst(array_pop($pieces))
      );
    }

    return $nameMap[$processorClass];
  }

  public static function routingKey(string $messageClass): string {
    static $keyMap = [];
    if (isset($keyMap[$messageClass]) === false) {
      $pieces = explode('\\', $messageClass);
      $keyMap[$messageClass] = sprintf(
        'courier.message:%s',
        lcfirst(array_pop($pieces))
      );
    }

    return $keyMap[$messageClass];
  }

  public static function create(
    string $processorClass,
    string $messageClass,
    string $routeName
  ): self {
    return new self(
      self::queueName($processorClass),
      self::routingKey($messageClass),
      $processorClass,
      $messageClass,
      $routeName
    );
  }

  public function __construct(
    string $queueName,
    string $routingKey,
    string $processorClass,
    string $messageClass,
    string $routeName
  ) {
    $this->queueName      = $queueName;
    $this->routingKey     = $routingKey;
    $this->processorClass = $processorClass;
    $this->messageClass   = $messageClass;
    $this->routeName      = $routeName;
  }

  public function getQueueName(): string {
    return $this->queueName;
  }

  public function getRoutingKey(): string {
    return $this->routingKey;
  }

  public function getProcessorClass(): string {
    return $this->processorClass;
  }

  public function getMessageClass(): string {
    return $this->messageClass;
  }

  public function getRouteName(): string {
    return $this->routeName;
  }
}
