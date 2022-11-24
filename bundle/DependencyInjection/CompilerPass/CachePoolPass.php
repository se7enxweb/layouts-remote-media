<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CachePoolPass implements CompilerPassInterface
{
    /**
     * Sets correct adapter service and provider for cache pool.
     */
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'netgen_layouts.remote_media.cache.adapter',
            $container->findDefinition(
                $container->getParameter('netgen_layouts.remote_media.cache.adapter_service_name'),
            ),
        );

        $provider = $container->getParameter('netgen_layouts.remote_media.cache.provider');

        if ($provider !== null) {
            $cacheService = $container->getDefinition('nglrm.cache');

            $cacheService->addTag(
                'cache.pool',
                [
                    'provider' => $provider,
                ],
            );
        }
    }
}
