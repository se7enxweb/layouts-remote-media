<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle;

use Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\CompilerPass\CachePoolPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenLayoutsRemoteMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new CachePoolPass(),
        );
    }
}
