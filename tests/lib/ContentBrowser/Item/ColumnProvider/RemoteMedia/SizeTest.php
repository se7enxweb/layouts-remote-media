<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class SizeTest extends TestCase
{
    private Size $sizeColumn;

    protected function setUp(): void
    {
        $this->sizeColumn = new Size();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: '05c849a775800b4d3e63f367594b4099',
            size: 586,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('586B', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInkB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'ef5f9800dbfba3dcf70eea0ee6949f7b',
            size: 1086,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('1.06kB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInMB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: '6bb630def95be3bd34aa7dbd0d564e8a',
            size: 269840548,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('257.34MB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInGB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'd7f6d6d649e3b9c3a190722ca2fff28e',
            size: 269840548462,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('251.31GB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInTB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: '680b24cf6eb900c4d178428301806916',
            size: 269840548462634,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('245.42TB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueInPB(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'b099205fc0313a39cc72918e9a82fa07',
            size: 269840548462634154,
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('239.67PB', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::prettyBytes
     */
    public function testGetValueWithNoSize(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'd237f07750422bcc2ab88f677bd5668b',
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('0B', $this->sizeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Size::getValue
     */
    public function testGetValueWithWrongItem(): void
    {
        $itemMock = $this->createMock(ItemInterface::class);

        self::assertNull($this->sizeColumn->getValue($itemMock));
    }
}
