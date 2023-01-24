<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueLoader;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource;
use Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaValueLoaderTest extends TestCase
{
    private MockObject $providerMock;

    private MockObject $entityManagerMock;

    private MockObject $layoutsRemoteResourceRepositoryMock;

    private RemoteMediaValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->layoutsRemoteResourceRepositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('getRepository')
            ->with(LayoutsRemoteResource::class)
            ->willReturn($this->layoutsRemoteResourceRepositoryMock);

        $this->valueLoader = new RemoteMediaValueLoader($this->providerMock, $this->entityManagerMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoadExisting(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

        $location = new RemoteResourceLocation($resource);

        $layoutsRemoteResource = new LayoutsRemoteResource('upload||video||folder|test_resource', $location);

        $this->layoutsRemoteResourceRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn($layoutsRemoteResource);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNewLocation(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

        $location = new RemoteResourceLocation($resource);

        $this->layoutsRemoteResourceRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn(null);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('store')
            ->with($resource)
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('storeLocation')
            ->with($location)
            ->willReturn($location);

        $layoutsRemoteResource = new LayoutsRemoteResource('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with($layoutsRemoteResource);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNewResource(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

        $location = new RemoteResourceLocation($resource);

        $this->layoutsRemoteResourceRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn(null);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|video|folder/test_resource')
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('store')
            ->with($resource)
            ->willReturn($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('storeLocation')
            ->with($location)
            ->willReturn($location);

        $layoutsRemoteResource = new LayoutsRemoteResource('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with($layoutsRemoteResource);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNotFound(): void
    {
        $this->layoutsRemoteResourceRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn(null);

        $this->providerMock
            ->expects(self::once())
            ->method('loadByRemoteId')
            ->with('upload|video|folder/test_resource')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|video|folder/test_resource')
            ->willThrowException(new RemoteResourceNotFoundException('upload|video|folder/test_resource'));

        self::assertNull($this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::loadByRemoteId
     */
    public function testloadByRemoteId(): void
    {
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

        $location = new RemoteResourceLocation($resource);

        $layoutsRemoteResource = new LayoutsRemoteResource('upload||video||folder|test_resource', $location);

        $this->layoutsRemoteResourceRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn($layoutsRemoteResource);

        self::assertSame($location, $this->valueLoader->loadByRemoteId('upload||video||folder|test_resource'));
    }
}
