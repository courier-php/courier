<?php
declare(strict_types = 1);

namespace Courier\Serializers;

use Courier\Contracts\Messages\MessageInterface;
use Courier\Contracts\Serializers\SerializerInterface;
use InvalidArgumentException;

class PhpSerializer implements SerializerInterface {
  public function getContentType(): string {
    return 'application/vnd.courier+php-serialized';
  }

  public function getContentEncoding(): string {
    return 'string';
  }

  public function serialize(MessageInterface $message): string {
    return serialize($message);
  }

  public function unserialize(string $data): MessageInterface {
    if (str_starts_with($data, 'O:') === false) {
      throw new InvalidArgumentException('Invalid serialized data');
    }

    $message = unserialize($data);

    if (in_array(MessageInterface::class, class_implements($message), true) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Class "%s" must implement "MessageInterface"',
          $message::class
        )
      );
    }

    return $message;
  }
}
