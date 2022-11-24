<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class CachePoolPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass::process
     */
    public function testCompilerPass(): void
    {
        $this->setDefinition('cache.app', new Definition());
        $this->setParameter('netgen_layouts.remote_media.cache.pool_name', 'cache.app');

        $this->compile();

        $this->assertContainerBuilderHasService(
            'netgen_layouts.remote_media.cache.pool',
        );
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass::process
     */
    public function testCompilerPassWithoutParameter(): void
    {
        $this->compile();

        $this->assertContainerBuilderNotHasService(
            'netgen_layouts.remote_media.cache.pool',
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CachePoolPass());
    }
}
