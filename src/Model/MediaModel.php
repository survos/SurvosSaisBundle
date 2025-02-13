<?php

namespace Survos\SaisBundle\Model;

use App\Entity\Media;
use Survos\SaisBundle\Service\SaisClientService;

class MediaModel
{
    public function __construct(
        public string $originalUrl, // the original URL of the image
        public string $root, // within sais
        public ?string $path=null, // the path of the source file relative to the storage
        public ?string $code=null, // the code that is used to uniquely id this media
    ) {
        if (!$this->code) {
            $this->code = $this->calculateCode();
        }
    }

    public function calculateCode(): string
    {
        return SaisClientService::calculateCode(url: $this->originalUrl . $this->root);
    }
}
