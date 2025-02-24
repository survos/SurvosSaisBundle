<?php // setup a User, need approxCount to determine path structure.

namespace Survos\SaisBundle\Model;

class AccountSetup
{
    public function __construct(
        public string $root,
        public int $approx,
        public ?string $mediaCallbackUrl = null, // e.g. for download
        public ?string $thumbCallbackUrl = null, // e.g. for resize, delete
        public ?string $apiKey = null,
    ) {
    }

    public function getApprox(): int
    {
        return $this->approx;
    }

    public function getRoot(): string
    {
        return $this->root;
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

}
