<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Item\RemoteMedia;

use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\Tests\Core\Service\TransactionRollback\TestCase;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

final class ItemTest extends TestCase
{
    private RemoteResourceLocation $location;

    private Item $item;

    protected function setUp(): void
    {
        $this->location = new RemoteResourceLocation(
            new RemoteResource([
                'remoteId' => 'upload|image|folder/test_resource',
                'type' => 'image',
                'url' => 'https://cloudinary.com/test/upload/image/folder/test_resource',
                'name' => 'test_resource',
                'folder' => Folder::fromPath('folder'),
            ]),
        );

        $this->item = new Item($this->location);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame('upload||image||folder|test_resource', $this->item->getValue());
    }

    /**
     * * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('test_resource', $this->item->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getType
     */
    public function testGetType(): void
    {
        self::assertSame('image', $this->item->getType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item::getRemoteResourceLocation
     */
    public function testGetRemoteResource(): void
    {
        self::assertSame(
            $this->location->getRemoteResource()->getRemoteId(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getRemoteId(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getType(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getType(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getUrl(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getUrl(),
        );

        self::assertInstanceOf(
            Folder::class,
            $this->location->getRemoteResource()->getFolder(),
        );

        self::assertInstanceOf(
            Folder::class,
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getFolder(),
        );

        self::assertSame(
            $this->location->getRemoteResource()->getFolder()->getPath(),
            $this->item->getRemoteResourceLocation()->getRemoteResource()->getFolder()->getPath(),
        );
    }
}
