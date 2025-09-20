<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Service;

use Survos\McpBundle\Service\McpClientService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SaisHttpClientService
{
    public function __construct(
        public readonly HttpClientInterface $httpClient,
        public readonly ?McpClientService $mcpClientService,
        public readonly string $apiEndpoint,
        public readonly string $apiKey,
    ) {}

    /**
     * Resolve a list of SAIS codes into full media rows.
     * Uses GET with comma-delimited ids for small batches (easier to debug),
     * falls back to POST for larger payloads.
     *
     * @param string[] $ids
     * @return array<int,array<string,mixed>>
     */
    public function fetchMediaByIds(array $ids): array
    {
        $ids = array_values(array_filter($ids, static fn($v) => $v !== null && $v !== ''));
        if ($ids === []) {
            return [];
        }

        $base = rtrim($this->apiEndpoint, '/');
        $headers = array_filter([
            'Accept'        => 'application/json',
            'Authorization' => $this->apiKey !== '' ? ('Bearer ' . $this->apiKey) : null,
        ]);

        $options = ['headers' => $headers];
        $host = parse_url($base, PHP_URL_HOST) ?: '';
        if ($host !== '' && str_ends_with($host, '.wip')) {
            // Symfony HttpClient picks up proxy from env vars by default.
            // Explicitly set to "default" so we don't skip it.
            $options['proxy'] = '127.0.0.1:7080';
        }

        // If the batch is small, use GET for easy manual repro / logging
        if (\count($ids) <= 50) {
            $q = http_build_query(['id' => join(',', $ids)]); // implode(',', $ids)]);
            $url = $base . '/fetch/media/by-ids?' . $q;
            $resp = $this->httpClient->request('GET', $url, $options);
        } else {
            $url = $base . '/fetch/media/by-ids';
            $options['json'] = ['ids' => $ids];
            $resp = $this->httpClient->request('POST', $url, $options);
        }

        $data = $resp->toArray(false);
        return $data;
    }
}
