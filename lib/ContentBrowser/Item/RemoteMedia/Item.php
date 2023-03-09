<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

use function str_replace;

final class Item implements ItemInterface
{
    public function __construct(
        private RemoteResourceLocation $location
    ) {
    }

    public function getValue(): string
    {
        return str_replace(['|', '/'], ['||', '|'], $this->location->getRemoteResource()->getRemoteId());
    }

    public function getName(): string
    {
        return $this->location->getRemoteResource()->getName();
    }

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function getType(): string
    {
        return $this->location->getRemoteResource()->getType();
    }

    public function getRemoteResourceLocation(): RemoteResourceLocation
    {
        return $this->location;
    }
}
