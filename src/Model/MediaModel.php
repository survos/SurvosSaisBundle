<?php

namespace Survos\SaisBundle\Model;

use App\Entity\Media;

class MediaModel
{
    public function __construct(
        public string $root,
        public array $images = [],
        public array $filters = [],
        public ?string $callbackUrl = null,
    ) {
    }

    public function getMediaObjects(): array
    {
        foreach ($this->images as $image) {
            $media = new MediaModel()
        }
    }
}
