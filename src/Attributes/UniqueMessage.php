<?php
declare(strict_types = 1);

namespace Courier\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS)]
class UniqueMessage {
  private string $attribute;
  private int $interval;
  private int $expiresAt;

  public function __construct(string $attribute = 'id', int $interval = 3600) {
    if (trim($attribute) === '') {
      throw new InvalidArgumentException('$attribute argument must not be empty');
    }

    if ($interval < 0) {
      throw new InvalidArgumentException('$interval argument must be a positive integer');
    }

    $this->attribute = $attribute;
    $this->interval = $interval;
    $this->expiresAt = time() + $interval;
  }

  public function getAttribute(): string {
    return $this->attribute;
  }

  public function getInterval(): int {
    return $this->interval;
  }

  public function getExpiresAt(): int {
    return $this->expiresAt;
  }
}
