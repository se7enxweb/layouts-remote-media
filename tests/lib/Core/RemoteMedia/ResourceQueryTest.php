<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Core\RemoteMedia;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\API\Values\Folder;
use PHPUnit\Framework\TestCase;

final class ResourceQueryTest extends TestCase
{
    private ResourceQuery $resourceQuery;

    protected function setUp(): void
    {
        $this->resourceQuery = ResourceQuery::createFromString('image|folder%2Fsubfolder|upload%7Cimage%7Cfolder%2Fsubfolder%2Fresource.jpg');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromString
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getRemoteId
     */
    public function testGetResourceId(): void
    {
        self::assertSame('upload|image|folder/subfolder/resource.jpg', $this->resourceQuery->getRemoteId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromString
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getType
     */
    public function testGetType(): void
    {
        self::assertSame('image', $this->resourceQuery->getType());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromString
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getFolder
     */
    public function testGetFolder(): void
    {
        self::assertSame(Folder::fromPath('folder/subfolder')->getPath(), $this->resourceQuery->getFolder()->getPath());
    }
}
