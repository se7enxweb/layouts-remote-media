<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class ResolutionTest extends TestCase
{
    private Resolution $resolutionColumn;

    protected function setUp(): void
    {
        $this->resolutionColumn = new Resolution();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValue(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
            'metadata' => [
                'width' => 1920,
                'height' => 1080,
            ],
        ]);

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('1920x1080', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithEmptyWidth(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
            'metadata' => [
                'width' => '',
                'height' => 1080,
            ],
        ]);

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithEmptyHeight(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
            'metadata' => [
                'width' => 1920,
                'height' => '',
            ],
        ]);

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithMissingKeys(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
        ]);

        $item = new RemoteMediaItem(new RemoteResourceLocation($resource));

        self::assertSame('', $this->resolutionColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Resolution::getValue
     */
    public function testGetValueWithWrongItem(): void
    {
        $itemMock = $this->createMock(ItemInterface::class);

        self::assertNull($this->resolutionColumn->getValue($itemMock));
    }
}
