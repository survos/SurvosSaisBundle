<?php

namespace Survos\SaisBundle\Model;

use OpenApi\Attributes as OA;

#[OA\Schema]
class AccountSetupResponse
{
    public function __construct(
        #[OA\Property(description: 'Generated API key', type: 'string')]
        public string $apiKey,
        
        #[OA\Property(description: 'Account configuration')]
        public AccountConfig $config,
        
        #[OA\Property(description: 'Setup timestamp', type: 'string', format: 'date-time')]
        public string $createdAt,
    ) {}
}
