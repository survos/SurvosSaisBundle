<?php

namespace Survos\SaisBundle\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
class AccountConfig
{
    public function __construct(
        public string $root,
        public int $storageDepth,
        public string $storagePath,
        public ?string $mediaCallbackUrl = null,
        public ?string $thumbCallbackUrl = null,
    ) {}
}
