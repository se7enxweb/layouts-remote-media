<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Core\RemoteMedia;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use PHPUnit\Framework\TestCase;

final class ResourceQueryTest extends TestCase
{
    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromValue
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getRemoteId
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getValue
     */
    public function testFromValue(): void
    {
        $resourceQuery = ResourceQuery::createFromValue('upload||image||folder|subfolder|resource.jpg');

        self::assertSame('upload||image||folder|subfolder|resource.jpg', $resourceQuery->getValue());
        self::assertSame('upload|image|folder/subfolder/resource.jpg', $resourceQuery->getRemoteId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::createFromRemoteId
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getRemoteId
     * @covers \Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery::getValue
     */
    public function testFromRemoteId(): void
    {
        $resourceQuery = ResourceQuery::createFromRemoteId('upload|image|folder/subfolder/resource.jpg');

        self::assertSame('upload||image||folder|subfolder|resource.jpg', $resourceQuery->getValue());
        self::assertSame('upload|image|folder/subfolder/resource.jpg', $resourceQuery->getRemoteId());
    }
}
