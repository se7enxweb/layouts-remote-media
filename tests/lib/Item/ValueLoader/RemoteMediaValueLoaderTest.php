<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueLoader;

use Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaValueLoaderTest extends TestCase
{
    private MockObject $providerMock;

    private RemoteMediaValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->valueLoader = new RemoteMediaValueLoader($this->providerMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoad(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willReturn($resource);

        self::assertSame($resource, $this->valueLoader->load('video|folder|upload%7Cvideo%7Cfolder%2Ftest_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoadNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willThrowException(
                new RemoteResourceNotFoundException('upload|video|folder/test_resource'),
            );

        self::assertNull($this->valueLoader->load('video|folder|upload%7Cvideo%7Cfolder%2Ftest_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::loadByRemoteId
     */
    public function testLoadByRemoteId(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willReturn($resource);

        self::assertSame($resource, $this->valueLoader->loadByRemoteId('video|folder|upload%7Cvideo%7Cfolder%2Ftest_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::loadByRemoteId
     */
    public function testLoadByRemoteIdNotFound(): void
    {
        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willThrowException(
                new RemoteResourceNotFoundException('upload|video|folder/test_resource'),
            );

        self::assertNull($this->valueLoader->loadByRemoteId('video|folder|upload%7Cvideo%7Cfolder%2Ftest_resource'));
    }
}
