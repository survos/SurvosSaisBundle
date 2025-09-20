<?php
declare(strict_types=1);

namespace Survos\SaisBundle;

use Survos\McpBundle\Service\McpClientService;
use Survos\SaisBundle\Command\SaisIterateCommand;
use Survos\SaisBundle\Command\SaisQueueCommand;
use Survos\SaisBundle\Command\SaisRegisterCommand;
use Survos\SaisBundle\Service\SaisClientService;
use Survos\SaisBundle\Service\SaisHttpClientService;
use Survos\SaisBundle\Twig\TwigExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class SurvosSaisBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('root')->defaultNull()->info('If not set, must be passed to each call')->end()
                ->scalarNode('api_endpoint')->defaultValue('https://sais.survos.com')->end()
                ->scalarNode('api_key')->defaultValue('')->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // SAIS client (hard dep on Symfony HttpClient)
        $builder->register(SaisHttpClientService::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true)
            ->setArgument('$httpClient', new Reference('http_client'))
            ->setArgument('$mcpClientService', new Reference(McpClientService::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->setArgument('$apiEndpoint', $config['api_endpoint'])
            ->setArgument('$apiKey', $config['api_key']);

        // Commands
        foreach ([SaisQueueCommand::class, SaisRegisterCommand::class, SaisIterateCommand::class] as $commandClass) {
            $builder->autowire($commandClass)
                ->setAutoconfigured(true)
                ->addTag('console.command');
        }

        foreach ([SaisClientService::class] as $class) {
            $builder->autowire($class)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->setAutowired(true);
        }

        // Twig
        $builder->autowire(TwigExtension::class)
            ->setArgument('$config', $config)
            ->addTag('twig.extension');
    }
}
