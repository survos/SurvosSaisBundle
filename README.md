# Survos Sais Bundle

A simple bundle that facilitates calling the Survos Async Image Service from an application

```bash
composer require survos/sais-bundle
```

@todo:
Get an API key at https://sais.survos.com and add it to your .env.local

```
# .env.local
SAIS_API_KEY=your-api-key
```

# Using the JSON RPC MCP Tool
See [JSONRPC.md](docs/JSONRPC.md) for detailed instructions on using the JSON RPC MCP Tool.

# Calls

Inject the service and make the calls

```php
    @todo: command that sends batches of images
    #[Route('/featured', name: 'app_list_featured_projects')]
    public function listFeatured(SaisClientService $saisService): Response
    {
        $payload = new \Survos\SaisBundle\Model\ProcessPayload(
            $images,
            ['small'],
            $this->urlGenerator->generate('app_webhook')
        );
        $saisService->dispatchProcess($payload);
    }

    #[Route('/webhook', name: 'app_webhook')]
    public function webHook(): Response
    {
        $data = ...
    }

```

