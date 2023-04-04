<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueLoader;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Netgen\Layouts\RemoteMedia\API\Values\RemoteMediaItem;
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

    private MockObject $remoteMediaItemRepositoryMock;

    private RemoteMediaValueLoader $valueLoader;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->remoteMediaItemRepositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('getRepository')
            ->with(RemoteMediaItem::class)
            ->willReturn($this->remoteMediaItemRepositoryMock);

        $this->valueLoader = new RemoteMediaValueLoader($this->providerMock, $this->entityManagerMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     */
    public function testLoadExisting(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '922adce6ceff0f0ab367cf321bdd1909',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->remoteMediaItemRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNewLocation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '5a4c5dd69f0c282cdec63a5a699d1d74',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $this->remoteMediaItemRepositoryMock
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

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNewResource(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '3c0c49aac4b5dd39ecf1cf6e6a6555ca',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $this->remoteMediaItemRepositoryMock
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

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->load('upload||video||folder|test_resource'));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::load
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueLoader\RemoteMediaValueLoader::resolveRemoteResource
     */
    public function testLoadNotFound(): void
    {
        $this->remoteMediaItemRepositoryMock
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
    public function testLoadByRemoteId(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '7646ae197b0fa3a85ccd8f48e35a600b',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource, 'netgen_layouts_value');

        $remoteMediaItem = new RemoteMediaItem('upload||video||folder|test_resource', $location);

        $this->remoteMediaItemRepositoryMock
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['value' => 'upload||video||folder|test_resource'])
            ->willReturn($remoteMediaItem);

        self::assertSame($location, $this->valueLoader->loadByRemoteId('upload||video||folder|test_resource'));
    }
}
