# ImageClient Bundle

A simple bundle that facilitates calling the Survos Image Service from an application

```bash
composer require survos/image-client-bundle
```

@todo:
Get an API key at https://images.survos.com and add it to your .env.local

```
# .env.local
IMAGE_SERVER_API_KEY=your-api-key
```

# Calls

Inject the service and make the calls

```php
    @todo: command that sends batches of images
    #[Route('/featured', name: 'app_list_featured_projects')]
    public function listFeatured(ImageClientService $imageClientService): Response
    {
        $callbackUrl = $this->urlGenerator...
        $urls = [] 
        $data = $imageClientService->addImages($urls, $filters, $callbackUrl);
        foreach ($data  .. update the database w
        
        );
    }

    #[Route('/webhook', name: 'app_webhook')]
    public function webHook(): Response
    {
        $data = ...
    }

```

