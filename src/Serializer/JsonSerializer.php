<?php
declare(strict_types = 1);

namespace Courier\Serializer;

use Courier\Exception\SerializeException;
use Courier\Exception\UnserializeException;
use JsonSerializable;

final class JsonSerializer implements SerializerInterface {
  private const CLASS_MARKER = '__class';
  private const PROPS_MARKER = '__props';

  private function markArray(array &$array): void {
    foreach ($array as $key => $value) {
      if (is_scalar($value) || $value === null) {
        continue;
      }

      if (is_array($value)) {
        $this->markArray($array[$key]);

        continue;
      }

      if (($value instanceof JsonSerializable) === false) {
        throw new SerializeException(
          sprintf(
            'Class "%s" must implement JsonSerializable interface',
            $value::class
          )
        );
      }

      $class = $value::class;
      $value = $value->jsonSerialize();
      $this->markArray($value);

      $array[$key] = [
        self::CLASS_MARKER => $class,
        self::PROPS_MARKER => $value
      ];
    }
  }

  private function unmarkArray(array &$array): void {
    foreach ($array as $key => $value) {
      if (is_scalar($value) || $value === null) {
        continue;
      }

      if (isset($value[self::CLASS_MARKER], $value[self::PROPS_MARKER]) === true) {
        if (class_exists($value[self::CLASS_MARKER]) === false) {
          throw new UnserializeException(
            sprintf(
              'Class "%s" does not exist',
              $value[self::CLASS_MARKER]
            )
          );
        }

        $this->unmarkArray($value[self::PROPS_MARKER]);

        $array[$key] = new $value[self::CLASS_MARKER](...$value[self::PROPS_MARKER]);

        continue;
      }

      $this->unmarkArray($array[$key]);
    }
  }

  public function getContentType(): string {
    return 'application/vnd.courier+json-serialized';
  }

  public function getContentEncoding(): string {
    return 'string';
  }

  public function serialize(array $array): string {
    $this->markArray($array);

    return json_encode($array, JSON_THROW_ON_ERROR);
  }

  public function unserialize(string $data): array {
    $array = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

    $this->unmarkArray($array);

    return $array;
  }
}
