<?php

declare(strict_types=1);

namespace Survos\SaisBundle\Service;

use Psr\Log\LoggerInterface;
use Survos\SaisBundle\Model\AccountSetup;
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
        return $data;
    }

    static public function calculateCode(string $url, string $root): string
    {
        return hash('xxh3', $url . $root);
    }

    static public function getBinNames()
    {
        return array_merge(range('0','9'), range('a', 'z'), range('A', 'Z'));
    }
    static public function calculateBinCount(int $approx): int
    {
        $bucketSize = 1000;
        $numOfBins = intdiv($approx, $bucketSize+1)+1; // bucket Size.  2K? 4k
        return $numOfBins;

    }
    static public function calculatePath(int $approx, ?string $xxh3=null, ?string $url=null, ?string $root=null): string
    {
        // @todo: check root / account to check if > 300_000 images, for using 2 digits (or even 1.5, first char + some bits)
        $xxh3 ??= self::calculateCode($url, $root);
        $map = self::getBinNames();

        // need root record to get approx count
        $num = hexdec($substr = substr($xxh3, 0, 8));
        $binCount = self::calculateBinCount($approx);
        // the modulo comes from the approx
        $bin = $num % $binCount;
        if ($bin < count($map)) {
            $prefix = $map[$bin];
        } else {
            $mapLength = count($map);
            $firstDigit = $map[intdiv($bin, $mapLength)];
            $secondDigit = $map[$bin % $mapLength];
            $prefix = $firstDigit . $secondDigit;
//            assert(false, "binCount: $binCount, $approx, @todo: 2-char prefix $num $substr for bin " . $bin);
        }

        return sprintf("%s/%s",
            $prefix,
            $xxh3
            );
//            substr($xxh3, 2, strlen($xxh3)));
    }

    public function accountSetup(AccountSetup $payload): ?array
    {
        // make the API call
        $path = '/account_setup';
        $method = 'POST';
        return $this->call($path, $method, $payload);

    }

    public function dispatchProcess(ProcessPayload $processPayload): ?array
    {
        // make the API call
        $path = '/dispatch_process';
        $method = 'POST';

        return $this->call($path, $method, $processPayload);

    }

    /**
     * @param string $path
     * @param string $method
     * @param ProcessPayload $payload
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function call(string $path, string $method, ProcessPayload|AccountSetup $payload): mixed
    {
        $url = $this->apiEndpoint . $path;
        $this->logger->info("Dispatching $url via " . $this->proxyUrl);
        $request = $this->httpClient->request($method, $url, [
                'proxy' => $this->proxyUrl,
                'json' => $payload,
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
            $this->logger->error("Error with $url", ['payload' => $payload]);
//            dd($request->getStatusCode(), $method, $url, $processPayload);
        }
        try {
            $content = $request->getContent();
        } catch (\Throwable $exception) {
            dd($exception->getMessage(), $url, $payload, $this->proxyUrl);
        }
        if (!$content) {
            $this->logger->error("Error with $url", ['url' => $url]);
            return null;
        }

        $data = json_decode($content, true);
        if (empty($data)) {
            $this->logger->error(sprintf("no data, %s on %s", $request->getStatusCode(), $url), [
//                'payload' => $processPayload,
            ]);
            dd($data, $content, $url, $payload);
        } else {
//            foreach ($data as $item) {
//                $this->logger->info($item['originalUrl'] . ": " . $item['marking']);
////                dd($item);
//            }
//            $this->logger->info($url, ['payload' => $processPayload, 'response' => $data]);
        }
        return $data;
    }

}
