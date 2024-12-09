<?php

declare(strict_types=1);

namespace Survos\SaisBundle\Service;

use Survos\SaisBundle\Model\ProcessPayload;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SaisClientService
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly ?string $apiKey = null,
        private readonly ?string $apiEndpoint = null,
        private readonly ?string $proxyUrl = 'https://127.0.0.1:7080'
    ) {
    }

    public function fetch(string $path, array $params = [], string $method='GET'): iterable
    {
        assert(in_array($method, ['GET', 'POST']));
        $request = $this->httpClient->request($method, $this->apiEndpoint . $path, [
                'query' => $params,
                'headers' => [
                    'authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ]]);
        if ($this->proxyUrl) {
        }
        if ($request->getStatusCode() !== 200) {

        }
        $data = json_decode($request->getContent(), true);
        return $data;
    }

    static public function calculateCode(string $url): string
    {
        return hash('xxh3', $url);
    }

    static public function calculatePath(?string $xxh3=null, ?string $url=null): string
    {
        $xxh3 ??= self::calculateCode($url);
        return sprintf("%s/%s/%s",
            substr($xxh3, 0, 2),
            substr($xxh3, 2, 2),
            substr($xxh3, 4, -1));
    }


    public function dispatchProcess(ProcessPayload $processPayload): iterable
    {
        // make the API call
        return $this->fetch('/dispatch_process/',
            [
                'urls' => $processPayload->images,
                'filters' => $processPayload->filters,
                'callbackUrl' => $processPayload->callbackUrl,
            ]);
    }

}
