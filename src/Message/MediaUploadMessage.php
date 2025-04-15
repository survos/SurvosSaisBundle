<?php

namespace Survos\SaisBundle\Message;

final class MediaUploadMessage
{

     public function __construct(
         private readonly string $url,
         private string $root,
         private ?string $code=null, // media code, not file code
         private readonly array $filters=[],
         private readonly ?string $callbackUrl=null,
         private readonly ?string $proxy='',
     ) {
     }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
