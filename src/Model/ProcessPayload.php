<?php

namespace Survos\SaisBundle\Model;

use App\Entity\Media;

class ProcessPayload
{
    public function __construct(
        public string $root,
        public array $images = [],
        public array $filters = [],
        public ?string $callbackUrl = null,
        public ?string $apiKey = null,
    ) {
    }

    public function getMediaObjects(): array
    {
        $objects = [];
        foreach ($this->images as $image) {
            $objects[] = new MediaModel(originalUrl: $image, root: $this->root);
        }
        return $objects;
    }
}
