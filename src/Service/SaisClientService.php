<?php

declare(strict_types=1);

namespace Survos\SaisBundle\Service;

use Psr\Log\LoggerInterface;
use Survos\SaisBundle\Model\ProcessPayload;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SaisClientService
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private readonly ?string $apiKey = null,
        private readonly ?string $apiEndpoint = null,
        # #[Autowire('%env(PROXY)%')]
        private ?string $proxyUrl = null
    ) {
        if (!$proxyUrl && str_contains($apiEndpoint, '.wip')) {
            $this->proxyUrl = '127.0.0.1:7080';
        }
        if ($this->proxyUrl) {
            assert(!str_contains($this->proxyUrl, 'http'), "no scheme in the proxy!");
        }
    }

    public function getApiEndpoint(): ?string
    {
        return $this->apiEndpoint;
    }

    public function getProxyUrl(): ?string
    {
        return $this->proxyUrl;
    }

    public function fetch(string $path, array $params = [], string $method='GET',
        string $accept = 'application/json'
    ): ?array
    {
        assert(in_array($method, ['GET', 'POST']));
        $url = $this->apiEndpoint . $path;
        $request = $this->httpClient->request($method, $url, [
            'proxy' => $this->proxyUrl,
                'query' => $params,
                'headers' => [
//                    'authorization' => $this->apiKey,
                    'Accept' => $accept,
                ]
        ]
        );
        if ($request->getStatusCode() !== 200) {
            return null;
        }
        $data = json_decode($request->getContent(), true);
        return $data;
    }

    public function post(string $path, array $params = [], string $method='GET'): iterable
    {
        assert(in_array($method, ['GET', 'POST']));
        $url = $this->apiEndpoint . $path;
        $request = $this->httpClient->request($method, $url, [
                'proxy' => $this->proxyUrl,
                'json' => $params,
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

    static public function calculateCode(string $url, string $root): string
    {
        return hash('xxh3', $url . $root);
    }

    static public function calculatePath(?string $xxh3=null, ?string $url=null, ?string $root=null): string
    {
        // @todo: check root / account to check if > 300_000 images, for using 2 digits (or even 1.5, first char + some bits)
        $xxh3 ??= self::calculateCode($url, $root);
        return sprintf("%s/%s",
            substr($xxh3, 0, 1),
//            substr($xxh3, 2, 2),
            $xxh3
            );
//            substr($xxh3, 2, strlen($xxh3)));
    }


    public function dispatchProcess(ProcessPayload $processPayload): ?array
    {
        // make the API call
        $path = '/dispatch_process';
        $method = 'POST';

        $url = $this->apiEndpoint . $path;
        $this->logger->info("Dispatching $url via " . $this->proxyUrl);
        $request = $this->httpClient->request($method, $url, [
                'proxy' => $this->proxyUrl,
                'json' => $processPayload,
                'headers' => [
//                    'authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                ]
            ]
        );

        try {
            $statusCode = $request->getStatusCode();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage() . "\n\n" . $url);
        }
        if ($request->getStatusCode() !== 200) {
            $this->logger->error("Error with $url", ['payload' => $processPayload]);
//            dd($request->getStatusCode(), $method, $url, $processPayload);
        }
        try {
            $content = $request->getContent();
        } catch (\Throwable $exception) {
            dd($exception->getMessage(), $url, $processPayload, $this->proxyUrl);
        }
        if (!$content) {
            $this->logger->error("Error with $url", ['url' => $url]);
            return null;
        }

        $data = json_decode($content, true);
        if (!$data) {
            $this->logger->error(sprintf("no data, %s on %s", $request->getStatusCode(), $url), [
                'payload' => $processPayload,
            ]);
        } else {
            $this->logger->info($url, ['payload' => $processPayload, 'response' => $data]);
        }
        return $data;

    }

}
