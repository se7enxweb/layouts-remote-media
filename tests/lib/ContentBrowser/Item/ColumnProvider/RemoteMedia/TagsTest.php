<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class TagsTest extends TestCase
{
    private Tags $tagsColumn;

    protected function setUp(): void
    {
        $this->tagsColumn = new Tags();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValue(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'htstps://cloudinary.com/test/upload/image/folder/test_resource',
            md5: '3c15a1d4bbcda8d067478a6316518acc',
            tags: ['tag1', 'tag2', 'tag3'],
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('tag1, tag2, tag3', $this->tagsColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValueWithNoTags(): void
    {
        $resource = new RemoteResource(
            remoteId: 'folder/test_resource',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/test_resource',
            md5: 'dc2474ad19a69be40dff3254af497d73',
        );

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('', $this->tagsColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Tags::getValue
     */
    public function testGetValueWithWrongItem(): void
    {
        $itemMock = $this->createMock(ItemInterface::class);

        self::assertNull($this->tagsColumn->getValue($itemMock));
    }
}
