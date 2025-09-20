<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Contract;

/**
 * The app provides selections (IDs / SAIS codes) to resolve via SAIS.
 * The command will POST these to SAIS and then dispatch resolved media rows.
 */
interface SelectionProviderInterface
{
    public function countSelections(?string $root = null): int;

    /**
     * Yield selection rows (lightweight). Each MUST include either:
     *   - ['saisCode' => '...']  OR  ['id' => '...']
     * You may include extra local context (e.g. grpId) if helpful downstream.
     *
     * @return iterable<array<string,mixed>>
     */
    public function getSelectionIterator(?string $root = null, int $offset = 0, ?int $limit = null): iterable;
}
