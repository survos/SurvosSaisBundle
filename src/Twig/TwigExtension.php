<?php

namespace Survos\ImageClientBundle\Twig;

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
            // If your filter generates SAFE HTML, add ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('survos_image_filter', fn (string $s, string $filter) => sprintf("%s/media/cache/%s/%s", $this->config['api_endpoint'], $filter, $s)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hasApiKey', fn() => !empty($this->config['api_key'])),
        ];
    }
}
