<?php

namespace Survos\SaisBundle;

use Survos\McpBundle\Service\McpClientService;
use Survos\SaisBundle\Command\SaisQueueCommand;
use Survos\SaisBundle\Command\SaisRegisterCommand;
use Survos\SaisBundle\Message\MediaUploadMessage;
use Survos\SaisBundle\Service\SaisClientService;
use Survos\SaisBundle\Twig\TwigExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosSaisBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->register(SaisClientService::class)
            ->setAutowired(true)
            ->setPublic(true)
            ->setAutoconfigured(true)
            ->setArgument('$httpClient', new Reference('http_client'))
            ->setArgument('$mcpClientService', new Reference(McpClientService::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->setArgument('$apiEndpoint', $config['api_endpoint'])
            ->setArgument('$apiKey', $config['api_key']);

        foreach ([SaisQueueCommand::class, SaisRegisterCommand::class] as $commandName) {
            $builder->autowire($commandName)
                ->setAutoconfigured(true)
                ->addTag('console.command')
            ;
        }

//        foreach ([MediaUploadMessage::class] as $messageClass) {
//            $builder->autowire($messageClass)
//                ->setPublic(true);
//        }

        $definition = $builder
            ->autowire(TwigExtension::class)
            ->setArgument('$config', $config)
            ->addTag('twig.extension');

    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('root')->info("if not set,  must be passed to each call")->end()
            ->scalarNode('api_endpoint')->defaultValue('https://sais.survos.com')->end()
            ->scalarNode('api_key')->defaultValue('')->end()
            ->end();
    }
}
