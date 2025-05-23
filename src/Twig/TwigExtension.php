<?php

namespace Survos\SaisBundle\Twig;

use Thumbhash\Thumbhash;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(private array $config)
    {
    }

    public function getFilters(): array
    {
        return [
            // this doesn't make sense anymore!
            // If your filter generates SAFE HTML, add ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('survos_image_filter', fn (string $path, ?string $root=null, string $filter='small') =>

                sprintf("%s/media/cache/%s/%s", $this->config['api_endpoint'],
                    $filter,
//                    $root ?? $this->config['root'],
                    $path
                )
            ),

        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hasApiKey', fn() => !empty($this->config['api_key'])),
            new TwigFunction('survos_image_info', fn (string $code) =>
            sprintf("%s/media/%s", $this->config['api_endpoint'], $code)
            ),
        ];
    }
}
