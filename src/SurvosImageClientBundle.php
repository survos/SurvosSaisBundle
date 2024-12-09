<?php

namespace Survos\ImageClientBundle;

use Survos\ImageClientBundle\Service\ImageClientService;
use Survos\ImageClientBundle\Twig\TwigExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosImageClientBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->autowire(ImageClientService::class)
            ->setPublic(true)
            ->setAutoconfigured(true)
            ->setArgument('$apiEndpoint', $config['api_endpoint'])
            ->setArgument('$apiKey', $config['api_key']);
        // $builder->setParameter('survos_workflow.direction', $config['direction']);

        // twig classes

        $definition = $builder
            ->autowire(TwigExtension::class)
            ->setArgument('$config', $config)
            ->addTag('twig.extension');

    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('api_endpoint')->defaultValue('https://images.survos.com')->end()
            ->scalarNode('api_key')->defaultValue('')->end()
            ->end();
    }
}
