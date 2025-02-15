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
        private readonly ?string $proxyUrl = '127.0.0.1:7080'
    ) {
        if ($proxyUrl) {
            assert(!str_contains($proxyUrl, 'http'), "no scheme in the proxy!");
        }
    }

    public function fetch(string $path, array $params = [], string $method='GET'): iterable
    {
        assert(in_array($method, ['GET', 'POST']));
        $url = $this->apiEndpoint . $path;
        $request = $this->httpClient->request($method, $url, [
            'proxy' => $this->proxyUrl,
                'query' => $params,
                'headers' => [
//                    'authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ]
        ]
        );
        $data = json_decode($request->getContent(), true);
        dd($data);
        return $data;
    }

    public function post(string $path, array $params = [], string $method='GET'): iterable
    {
        assert(in_array($method, ['GET', 'POST']));
        $url = $this->apiEndpoint . $path;
        $request = $this->httpClient->request($method, $url, [
                'proxy' => $this->proxyUrl,
                'data' => $params,
                'headers' => [
//                    'authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ]
            ]
        );
        $data = json_decode($request->getContent(), true);
        dd($data);
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
        $path = '/dispatch_process';
        $method = 'POST';

        $url = $this->apiEndpoint . $path;
        dump(json_encode($processPayload));
        $request = $this->httpClient->request($method, $url, [
                'proxy' => $this->proxyUrl,
                'json' => $processPayload,
                'headers' => [
//                    'authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ]
            ]
        );
        $data = json_decode($request->getContent(), true);
        return $data;

    }

}
