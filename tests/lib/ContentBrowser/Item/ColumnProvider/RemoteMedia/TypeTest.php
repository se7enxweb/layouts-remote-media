<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    private Type $typeColumn;

    protected function setUp(): void
    {
        $this->typeColumn = new Type();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type::getValue
     */
    public function testGetValue(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'fd03486b8f6fcdf3d60fd124465ec8d8',
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('image', $this->typeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type::getValue
     */
    public function testGetValueWithFormat(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'ddd1248ff21c4f16c5839fffe3f6a51d',
            metadata: ['format' => 'mp4'],
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('video / mp4', $this->typeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type::getValue
     */
    public function testGetValueWithWrongItem(): void
    {
        $itemMock = $this->createMock(ItemInterface::class);

        self::assertNull($this->typeColumn->getValue($itemMock));
    }
}
