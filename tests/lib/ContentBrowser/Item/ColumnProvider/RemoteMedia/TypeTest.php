<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\ColumnProvider\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item as RemoteMediaItem;
use Netgen\RemoteMedia\API\Values\RemoteResource;
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
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
        ]);

        $item = new RemoteMediaItem($resource);

        self::assertSame('image', $this->typeColumn->getValue($item));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\ColumnProvider\RemoteMedia\Type::getValue
     */
    public function testGetValueWithFormat(): void
    {
        $resource = new RemoteResource([
            'remoteId' => 'folder/test_resource',
            'type' => 'video',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
            'metadata' => ['format' => 'mp4'],
        ]);

        $item = new RemoteMediaItem($resource);

        self::assertSame('video / mp4', $this->typeColumn->getValue($item));
    }
}
