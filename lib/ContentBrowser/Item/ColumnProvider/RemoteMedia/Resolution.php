<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;

final class Resolution implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        if (($item->getRemoteResource()->metaData['width'] ?? '') === '') {
            return '';
        }

        if (($item->getRemoteResource()->metaData['height'] ?? '') === '') {
            return '';
        }

        return $item->getRemoteResource()->metaData['width'] . 'x' . $item->getRemoteResource()->metaData['height'];
    }
}
