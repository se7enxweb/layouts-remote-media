<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\API\Values;

use Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class RemoteMediaItemTest extends TestCase
{
    /**
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem::__construct
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem::getId
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem::getRemoteResourceLocation
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem::getValue
     */
    public function test(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|media/example',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/media/example',
            md5: '1160cdbeea28403049e282452a4c27ff',
            name: 'example',
        );
        $location = new RemoteResourceLocation(
            $resource,
            'netgen_layouts',
            [
                new CropSettings('small', 50, 80, 800, 400),
                new CropSettings('medium', 30, 50, 1200, 600),
                new CropSettings('large', 10, 25, 2000, 1000),
            ],
        );

        $remoteMediaItem = new RemoteMediaItem('upload||image||media|example', $location);

        self::assertSame(
            'upload||image||media|example',
            $remoteMediaItem->getValue(),
        );

        self::assertNull($remoteMediaItem->getId());

        self::assertSame(
            $location->getCropSettings(),
            $remoteMediaItem->getRemoteResourceLocation()->getCropSettings(),
        );

        self::assertSame(
            $resource->getRemoteId(),
            $remoteMediaItem->getRemoteResourceLocation()->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $resource->getUrl(),
            $remoteMediaItem->getRemoteResourceLocation()->getRemoteResource()->getUrl(),
        );
    }
}
