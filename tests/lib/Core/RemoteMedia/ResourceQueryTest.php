<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Core\RemoteMedia;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use PHPUnit\Framework\TestCase;

final class ResourceQueryTest extends TestCase
{
    private ResourceQuery $resourceQuery;

    protected function setUp(): void
    {
        $this->resourceQuery = ResourceQuery::createFromString('image|folder|resource.jpg');
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromString
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getResourceId
     */
    public function testGetResourceId(): void
    {
        self::assertSame('folder/resource.jpg', $this->resourceQuery->getResourceId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getResourceType
     */
    public function testgetType(): void
    {
        self::assertSame('image', $this->resourceQuery->getType());
    }
}
