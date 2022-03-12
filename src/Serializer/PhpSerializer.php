<?php
declare(strict_types = 1);

namespace Courier\Serializer;

use Courier\Exception\SerializeException;
use Courier\Exception\UnserializeException;
use JsonSerializable;

final class PhpSerializer implements SerializerInterface {

  public function getContentType(): string {
    return 'application/vnd.courier+php-serialized';
  }

  public function getContentEncoding(): string {
    return 'string';
  }

  public function serialize(array $array): string {
    return serialize($array);
  }

  public function unserialize(string $data): array {
    return unserialize($data);
  }
}
