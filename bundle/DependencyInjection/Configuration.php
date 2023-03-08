<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_layouts_remote_media');

        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $this->addCacheConfiguration($rootNode);

        return $treeBuilder;
    }

    private function addCacheConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->scalarNode('pool')
                            ->cannotBeEmpty()
                            ->defaultValue('cache.app')
                        ->end()
                        ->integerNode('ttl')
                            ->min(30)
                            ->defaultValue(7200)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
