<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;

use function round;

final class Size implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof Item) {
            return null;
        }

        return $this->prettyBytes($item->getRemoteResource()->size);
    }

    private function prettyBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;

        $unitIndex = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            ++$unitIndex;
        }

        return round($size, $precision) . ($units[$unitIndex] ?? '');
    }
}
