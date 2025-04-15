<?php // what's sent to the server

namespace Survos\SaisBundle\Model;

use App\Entity\Media;

class ProcessPayload
{
    public function __construct(
        public string  $root,
        public array   $images = [],
        public array   $filters = [],
        public array   $context = [], // passed back in callback, but shouldn't be used by sais

        public ?string $mediaCallbackUrl = null, // e.g. for download
        public ?string $thumbCallbackUrl = null, // e.g. for resize, delete
        public ?string $apiKey = null,
        public ?bool   $wait = null,
    )
    {
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

    /**
     * @return MediaModel[]
     */
    public function getMediaObjects(): array
    {
        $objects = [];
        foreach ($this->images as $image) {

            if (is_string($image)) {
                $image = new MediaModel($image, $this->root);
            } else {
                $image = (object) $image;
                $image = new MediaModel($image->url, $this->root);
            }
            $objects[] = $image;
        }
        return $objects;
    }
}
