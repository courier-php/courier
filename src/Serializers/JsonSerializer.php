<?php
declare(strict_types = 1);

namespace Courier\Serializers;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Serializers\SerializerInterface;
use InvalidArgumentException;

class JsonSerializer implements SerializerInterface {
  public function getContentType(): string {
    return 'application/vnd.courier+json-serialized';
  }

  public function getContentEncoding(): string {
    return 'string';
  }

  public function serialize(MessageInterface $message): string {
    return json_encode(
      [
        $message::class,
        $message->getPayload(),
        $message->getAttributes()
      ],
      JSON_INVALID_UTF8_IGNORE |
      JSON_PRESERVE_ZERO_FRACTION |
      JSON_UNESCAPED_SLASHES |
      JSON_UNESCAPED_UNICODE
    );
  }

  public function unserialize(string $data): MessageInterface {
    [$class, $payload, $attributes] = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
    if (class_exists($class) === false) {
      throw new InvalidArgumentException(
        "Class \"{$class}\" was not found"
      );
    }

    if (in_array(MessageInterface::class, class_implements($class), true) === false) {
      throw new InvalidArgumentException(
        "Class \"{$class}\" must implement \"MessageInterface\""
      );
    }

    $message = new $class(...$payload);
    $message->setAttributes($attributes);

    return $message;
  }
}
