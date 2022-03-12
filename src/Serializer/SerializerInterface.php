<?php
declare(strict_types = 1);

namespace Courier\Serializer;

interface SerializerInterface {
  public function getContentType(): string;
  public function getContentEncoding(): string;
  public function serialize(array $array): string;
  public function unserialize(string $data): array;
}
