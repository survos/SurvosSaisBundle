<?php // what's sent to the server

namespace Survos\SaisBundle\Model;

use App\Entity\Media;

class ProcessPayload
{
    public function __construct(
        public string $root,
        public array $images = [],
        public array $filters = [],
        public ?string $mediaCallbackUrl = null, // e.g. for download
        public ?string $thumbCallbackUrl = null, // e.g. for resize, delete
        public ?string $apiKey = null,
    ) {
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getMediaCallbackUrl(): ?string
    {
        return $this->mediaCallbackUrl;
    }

    public function getThumbCallbackUrl(): ?string
    {
        return $this->thumbCallbackUrl;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
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
