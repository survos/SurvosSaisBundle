<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Emitted with fully-resolved media rows (after SAIS HTTP resolution).
 */
final class IterateBatchEvent extends Event
{
    /**
     * @param array<int,array<string,mixed>> $batch  Normalized media rows from SAIS
     */
    public function __construct(
        public readonly string $root,
        public readonly array $batch,
        public readonly int $offset, // informational; 0 in selection mode
        public readonly int $count,  // count for this dispatch
        public readonly ?array $meta = null,
    ) {}
}
