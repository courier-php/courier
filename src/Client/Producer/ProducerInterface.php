<?php
declare(strict_types = 1);

namespace Courier\Client\Producer;

use Courier\Interceptor\ProducerInterceptorInterface;
use Courier\Message\CommandInterface;
use Courier\Message\EventInterface;
use Courier\Middleware\MiddlewareInterface;

interface ProducerInterface {
  public function addMiddleware(MiddlewareInterface $middleware): static;

  public function setInterceptor(?ProducerInterceptorInterface $producerInterceptor = null): static;

  public function getInterceptor(): ?ProducerInterceptorInterface;

  public function sendEvent(EventInterface $event): void;

  /**
   * @throws \LogicException
   */
  public function sendCommand(CommandInterface $command): void;
}
