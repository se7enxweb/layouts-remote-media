<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function array_pop;
use function explode;
use function implode;
use function str_replace;

final class Item implements ItemInterface
{
    private RemoteResource $resource;

    public function __construct(RemoteResource $resource)
    {
        $this->resource = $resource;
    }

    public function getValue(): string
    {
        return implode('|', [
            $this->resource->getType(),
            urlencode((string) $this->resource->getFolder()),
            urlencode($this->resource->getRemoteId()),
        ]);
    }

    public function getName(): string
    {
        $parts = explode('/', $this->resource->getRemoteId());

        return array_pop($parts);
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
        return $this->resource->getType();
    }

    public function getRemoteResource(): RemoteResource
    {
        return $this->resource;
    }
}
