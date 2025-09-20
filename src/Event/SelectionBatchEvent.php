<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Emitted right before resolving a batch of selection IDs via SAIS.
 */
final class SelectionBatchEvent extends Event
{
    /** @param string[] $ids */
    public function __construct(
        public readonly string $root,
        public readonly array $ids,
        public readonly ?array $meta = null,
    ) {}
}
