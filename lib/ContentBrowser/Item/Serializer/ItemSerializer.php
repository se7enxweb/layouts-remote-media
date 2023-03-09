<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\Serializer;

use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\ContentBrowser\Item\Serializer\ItemSerializerInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;

final class ItemSerializer implements ItemSerializerInterface
{
    public function __construct(
        private ItemSerializerInterface $innerItemSerializer,
        private BackendInterface $backend,
        private ColumnProviderInterface $columnProvider
    ) {
    }

    public function serializeItem(ItemInterface $item): array
    {
        if (!$item instanceof Item) {
            return $this->innerItemSerializer->serializeItem($item);
        }

        return [
            'location_id' => null,
            'value' => $item->getValue(),
            'name' => $item->getName(),
            'visible' => $item->isVisible(),
            'selectable' => $item->isSelectable(),
            'has_sub_items' => false,
            'columns' => $this->columnProvider->provideColumns($item),
        ];
    }

    public function serializeLocation(LocationInterface $location): array
    {
        if (!$location instanceof Location) {
            return $this->innerItemSerializer->serializeLocation($location);
        }

        return [
            'id' => $location->getLocationId(),
            'parent_id' => $location->getParentId(),
            'name' => $location->getName(),
            'has_sub_items' => true,
            'has_sub_locations' => $this->backend->getSubLocationsCount($location) > 0,
            // Used exclusively to display columns for parent location
            'visible' => true,
            'columns' => [
                'name' => $location->getName(),
            ],
        ];
    }
}
