<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RemoteMediaExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'netgen_layouts_remote_media_block_variation',
                [RemoteMediaRuntime::class, 'getBlockVariation'],
            ),
            new TwigFunction(
                'netgen_layouts_remote_media_item_variation',
                [RemoteMediaRuntime::class, 'getItemVariation'],
            ),
            new TwigFunction(
                'netgen_layouts_remote_media_block_tag',
                [RemoteMediaRuntime::class, 'getBlockTag'],
            ),
            new TwigFunction(
                'netgen_layouts_remote_media_item_tag',
                [RemoteMediaRuntime::class, 'getItemTag'],
            ),
        ];
    }
}
