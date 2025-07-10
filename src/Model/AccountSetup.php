<?php 

namespace Survos\SaisBundle\Model;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(required: ['root', 'approx'])] // corrected 'estimated' to 'approx'
class AccountSetup
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 50)]
        #[OA\Property(description: 'The root code that prefixes the file storage', type: 'string', maxLength: 50, minLength: 3, nullable: false)]
        public string $root,
        #[Assert\NotBlank]
        #[OA\Property(description: 'The estimated number of images', type: 'integer', nullable: false)]
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
