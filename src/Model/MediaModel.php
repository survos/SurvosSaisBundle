<?php

namespace Survos\SaisBundle\Model;

use Survos\SaisBundle\Service\SaisClientService;

class MediaModel
{
    public ?int $size = null;
    public ?array $resized = null;
    public ?string $path=null; // the path of the source file relative to the storage

    public function __construct(
        public string $originalUrl, // the original URL of the image
        public ?string $code=null, // the code that is used to uniquely id this media
    ) {
        if (!$this->code) {
            $this->code = SaisClientService::calculateCode(url: $this->originalUrl);
        }
    }
}
