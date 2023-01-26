<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;

final class Type implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        $value = $item->getRemoteResourceLocation()->getRemoteResource()->getType();

        $format = $item->getRemoteResourceLocation()->getRemoteResource()->getMetadataProperty('format') ?? '';

        if ($format !== '') {
            $value .= ' / ' . $item->getRemoteResourceLocation()->getRemoteResource()->getMetadataProperty('format');
        }

        return $value;
    }
}
