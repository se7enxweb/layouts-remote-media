<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\API\Values;

use Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource;
use Netgen\RemoteMedia\API\Values\CropSettings;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class LayoutsRemoteResourceTest extends TestCase
{
    /**
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource::__construct
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource::getId
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource::getRemoteResourceLocation
     * @covers \Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource::getValue
     */
    public function test(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'upload|image|media/example',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/media/example',
            'name' => 'example',
        ]);

        $location = new RemoteResourceLocation(
            $resource,
            [
                new CropSettings('small', 50, 80, 800, 400),
                new CropSettings('medium', 30, 50, 1200, 600),
                new CropSettings('large', 10, 25, 2000, 1000),
            ],
        );

        $layoutsRemoteResource = new LayoutsRemoteResource('upload||image||media|example', $location);

        self::assertSame(
            'upload||image||media|example',
            $layoutsRemoteResource->getValue(),
        );

        self::assertNull($layoutsRemoteResource->getId());

        self::assertSame(
            $location->getCropSettings(),
            $layoutsRemoteResource->getRemoteResourceLocation()->getCropSettings(),
        );

        self::assertSame(
            $resource->getRemoteId(),
            $layoutsRemoteResource->getRemoteResourceLocation()->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $resource->getUrl(),
            $layoutsRemoteResource->getRemoteResourceLocation()->getRemoteResource()->getUrl(),
        );
    }
}
