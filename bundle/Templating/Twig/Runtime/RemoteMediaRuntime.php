<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime;

use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use Twig\Extension\AbstractExtension;

final class RemoteMediaRuntime extends AbstractExtension
{
    protected ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getBlockVariation(RemoteResourceLocation $remoteResourceLocation, string $variation): RemoteResourceVariation
    {
        return $this->provider->buildVariation($remoteResourceLocation, 'netgen_layouts_block', $variation);
    }

    public function getItemVariation(RemoteResourceLocation $remoteResourceLocation, string $variation): RemoteResourceVariation
    {
        return $this->provider->buildVariation($remoteResourceLocation, 'netgen_layouts_item', $variation);
    }

    public function getBlockTag(RemoteResourceLocation $remoteResourceLocation, ?string $variation = null, bool $useThumbnail = false): string
    {
        if ($variation) {
            return $this->provider->generateVariationHtmlTag(
                $remoteResourceLocation,
                'netgen_layouts_block',
                $variation,
                [],
                true,
                $useThumbnail
            );
        }

        return $this->provider->generateHtmlTag(
            $remoteResourceLocation->getRemoteResource(),
            [],
            true,
            $useThumbnail
        );
    }

    public function getItemTag(RemoteResourceLocation $remoteResourceLocation, ?string $variation = null, bool $useThumbnail = false): string
    {
        if ($variation) {
            return $this->provider->generateVariationHtmlTag(
                $remoteResourceLocation,
                'netgen_layouts_item',
                $variation,
                [],
                true,
                $useThumbnail
            );
        }

        return $this->provider->generateHtmlTag(
            $remoteResourceLocation->getRemoteResource(),
            [],
            true,
            $useThumbnail
        );
    }
}
