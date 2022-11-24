<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_layouts_remote_media');
        $this->addCacheConfiguration($treeBuilder->getRootNode());

        return $treeBuilder;
    }

    private function addCacheConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('cache')
            ->children()
            ->scalarNode('adapter')
            ->isRequired()
            ->cannotBeEmpty()
            ->defaultValue('cache.adapter.filesystem')
            ->end()
            ->scalarNode('provider')
            ->defaultNull()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
