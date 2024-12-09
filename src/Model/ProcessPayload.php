<?php

namespace Survos\ImageClientBundle\Model;

class ProcessPayload
{
    public function __construct(
        public array $images = [],
        public array $filters = [],
        public ?string $callbackUrl = null,
    ) {
    }
}
