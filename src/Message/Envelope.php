<?php
declare(strict_types = 1);

namespace Courier\Message;

use DateTimeImmutable;

final class Envelope {
  private string $appId;
  private string $body;
  private string $contentEncoding;
  private string $contentType;
  private string $correlationId;
  private EnvelopeDeliveryModeEnum $deliveryMode;
  private ?int $deliveryTag;
  private string $exchange;
  private string $expiration;
  /**
   * @var array<string, mixed>
   */
  private array $headers = [];
  private bool $isRedelivery;
  private string $messageId;
  private EnvelopePriorityEnum $priority;
  private string $replyTo;
  private string $routingKey;
  private ?DateTimeImmutable $timestamp;
  private string $type;
  private string $userId;

  /**
   * @param array{
   *   appId?: string,
   *   contentEncoding?: string,
   *   contentType?: string,
   *   correlationId?: string,
   *   deliveryMode?: \Courier\Message\EnvelopeDeliveryModeEnum,
   *   deliveryTag?: int,
   *   exchange?: string,
   *   expiration?: string,
   *   headers?: array<string, mixed>,
   *   isRedelivery?: bool,
   *   messageId?: string,
   *   priority?: \Courier\Message\EnvelopePriorityEnum,
   *   replyTo?: string,
   *   routing_key?: string,
   *   timestamp?: \DateTimeImmutable|null,
   *   type?: string,
   *   userId?: string
   * } $properties
   */
  public static function fromArray(string $body = '', array $properties = []): self {
    return new self(
      $properties['appId'] ?? '',
      $body,
      $properties['contentEncoding'] ?? '',
      $properties['contentType'] ?? '',
      $properties['correlationId'] ?? '',
      $properties['deliveryMode'] ?? EnvelopeDeliveryModeEnum::TRANSIENT,
      $properties['deliveryTag'] ?? null,
      $properties['exchange'] ?? '',
      $properties['expiration'] ?? '',
      $properties['headers'] ?? [],
      $properties['isRedelivery'] ?? false,
      $properties['messageId'] ?? '',
      $properties['priority'] ?? EnvelopePriorityEnum::Normal,
      $properties['replyTo'] ?? '',
      $properties['routing_key'] ?? '',
      $properties['timestamp'] ?? null,
      $properties['type'] ?? '',
      $properties['userId'] ?? ''
    );
  }

  public function __construct(
    string $appId = '',
    string $body = '',
    string $contentEncoding = '',
    string $contentType = '',
    string $correlationId = '',
    EnvelopeDeliveryModeEnum $deliveryMode = EnvelopeDeliveryModeEnum::TRANSIENT,
    ?int $deliveryTag = null,
    string $exchange = '',
    string $expiration = '',
    array $headers = [],
    bool $isRedelivery = false,
    string $messageId = '',
    EnvelopePriorityEnum $priority = EnvelopePriorityEnum::Normal,
    string $replyTo = '',
    string $routingKey = '',
    DateTimeImmutable $timestamp = null,
    string $type = '',
    string $userId = ''
  ) {
    $this->appId           = $appId;
    $this->body            = $body;
    $this->contentEncoding = $contentEncoding;
    $this->contentType     = $contentType;
    $this->correlationId   = $correlationId;
    $this->deliveryMode    = $deliveryMode;
    $this->deliveryTag     = $deliveryTag;
    $this->exchange        = $exchange;
    $this->expiration      = $expiration;
    $this->headers         = $headers;
    $this->isRedelivery    = $isRedelivery;
    $this->messageId       = $messageId;
    $this->priority        = $priority;
    $this->replyTo         = $replyTo;
    $this->routingKey      = $routingKey;
    $this->timestamp       = $timestamp;
    $this->type            = $type;
    $this->userId          = $userId;
  }

  public function getAppId(): string {
    return $this->appId;
  }

  public function withAppId(string $appId): self {
    $clone = clone $this;
    $clone->appId = $appId;

    return $clone;
  }

  public function getBody(): string {
    return $this->body;
  }

  public function withBody(string $body): self {
    $clone = clone $this;
    $clone->body = $body;

    return $clone;
  }

  public function getContentEncoding(): string {
    return $this->contentEncoding;
  }

  public function withContentEncoding(string $contentEncoding): self {
    $clone = clone $this;
    $clone->contentEncoding = $contentEncoding;

    return $clone;
  }

  public function getContentType(): string {
    return $this->contentType ?: 'text/plain';
  }

  public function withContentType(string $contentType): self {
    $clone = clone $this;
    $clone->contentType = $contentType;

    return $clone;
  }

  public function getCorrelationId(): string {
    return $this->correlationId;
  }

  public function withCorrelationId(string $correlationId): self {
    $clone = clone $this;
    $clone->correlationId = $correlationId;

    return $clone;
  }

  public function getDeliveryMode(): EnvelopeDeliveryModeEnum {
    return $this->deliveryMode;
  }

  public function withDeliveryMode(EnvelopeDeliveryModeEnum $deliveryMode): self {
    $clone = clone $this;
    $clone->deliveryMode = $deliveryMode;

    return $clone;
  }

  public function getDeliveryTag(): ?int {
    return $this->deliveryTag;
  }

  public function withDeliveryTag(int $deliveryTag): self {
    $clone = clone $this;
    $clone->deliveryTag = $deliveryTag;

    return $clone;
  }

  public function getExchange(): string {
    return $this->exchange;
  }

  public function withExchange(string $exchange): self {
    $clone = clone $this;
    $clone->exchange = $exchange;

    return $clone;
  }

  public function getExpiration(): string {
    return $this->expiration;
  }

  public function withExpiration(string $expiration): self {
    $clone = clone $this;
    $clone->expiration = $expiration;

    return $clone;
  }

  public function getHeader(string $header): string {
    return $this->headers[$header] ?? '';
  }

  /**
   * @param mixed $value
   */
  public function withHeader(string $header, $value): self {
    $clone = clone $this;
    $clone->headers[$header] = $value;

    return $clone;
  }

  /**
   * @return array<string, mixed>
   */
  public function getHeaders(): array {
    return $this->headers;
  }

  /**
   * @param array<string, mixed> $headers
   */
  public function withHeaders(array $headers): self {
    $clone = clone $this;
    $clone->headers = array_merge($clone->headers, $headers);

    return $clone;
  }

  public function getMessageId(): string {
    return $this->messageId;
  }

  public function withMessageId(string $messageId): self {
    $clone = clone $this;
    $clone->messageId = $messageId;

    return $clone;
  }

  public function getPriority(): EnvelopePriorityEnum {
    return $this->priority;
  }

  public function withPriority(EnvelopePriorityEnum $priority): self {
    $clone = clone $this;
    $clone->priority = $priority;

    return $clone;
  }

  public function getReplyTo(): string {
    return $this->replyTo;
  }

  public function withReplyTo(string $replyTo): self {
    $clone = clone $this;
    $clone->replyTo = $replyTo;

    return $clone;
  }

  public function getRoutingKey(): string {
    return $this->routingKey ?: '';
  }

  public function withRoutingKey(string $routingKey): self {
    $clone = clone $this;
    $clone->routingKey = $routingKey;

    return $clone;
  }

  public function getTimestamp(): ?DateTimeImmutable {
    return $this->timestamp;
  }

  public function withTimestamp(?DateTimeImmutable $timestamp): self {
    $clone = clone $this;
    $clone->timestamp = $timestamp;

    return $clone;
  }

  public function getType(): string {
    return $this->type;
  }

  public function withType(string $type): self {
    $clone = clone $this;
    $clone->type = $type;

    return $clone;
  }

  public function getUserId(): string {
    return $this->userId;
  }

  public function withUserId(string $userId): self {
    $clone = clone $this;
    $clone->userId = $userId;

    return $clone;
  }

  public function isRedelivery(): bool {
    return $this->isRedelivery ?? false;
  }

  public function withRedelivery(bool $redelivery): self {
    $clone = clone $this;
    $clone->isRedelivery = $redelivery;

    return $clone;
  }

  /**
   * @return array{
   *   appId: string,
   *   correlationId: string,
   *   expiration: string,
   *   headers: array<string, mixed>,
   *   isRedelivery: bool,
   *   messageId: string,
   *   priority: \Courier\Message\EnvelopePriorityEnum,
   *   replyTo: string,
   *   timestamp: \DateTimeImmutable|null,
   *   type: string,
   *   userId: string
   * }
   */
  public function getAttributes(): array {
    return [
      'appId'         => $this->appId,
      'correlationId' => $this->correlationId,
      'expiration'    => $this->expiration,
      'headers'       => $this->headers,
      'isRedelivery'  => $this->isRedelivery,
      'messageId'     => $this->messageId,
      'priority'      => $this->priority,
      'replyTo'       => $this->replyTo,
      'timestamp'     => $this->timestamp,
      'type'          => $this->type,
      'userId'        => $this->userId
    ];
  }
}
