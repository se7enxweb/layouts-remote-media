<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\Tests\Core\Service\TransactionRollback\TestCase;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\CloudinaryRemoteId;

final class ItemTest extends TestCase
{
    private RemoteResource $resource;

    private Item $item;

    protected function setUp(): void
    {
        $this->resource = new RemoteResource([
            'remoteId' => 'upload|image|folder/test_resource',
            'type' => 'image',
            'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
        ]);

        $this->item = new Item($this->resource);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame('image|folder|upload|image|folder|test_resource', $this->item->getValue());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('test_resource', $this->item->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getResourceType
     */
    public function testGetType(): void
    {
        self::assertSame('image', $this->item->getType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getRemoteMediaValue
     */
    public function testGetRemoteResource(): void
    {
        self::assertSame(
            $this->resource->getRemoteId(),
            $this->item->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $this->resource->getType(),
            $this->item->getRemoteResource()->getType(),
        );

        self::assertSame(
            $this->resource->getUrl(),
            $this->item->getRemoteResource()->getUrl(),
        );
    }
}
