<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Message;

/**
 * A lightweight cross-app event that SAIS publishes and MUS consumes.
 * Keep this stable & framework-agnostic so both apps can share it.
 */
final class SaisEventMessage
{
    public function __construct(
        public string $event,             // e.g. "obra.updated"
        public mixed $payload,            // normalized data (ids, fields, etc.)
        public ?string $correlationId = null, // to trace across systems
        public ?string $sourceApp = 'sais',   // default publisher
        public ?\DateTimeImmutable $emittedAt = null,
    ) {
        $this->emittedAt ??= new \DateTimeImmutable('now');
    }
}
