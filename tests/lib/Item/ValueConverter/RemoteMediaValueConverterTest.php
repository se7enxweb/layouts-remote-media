<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueConverter;

use Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;
use stdClass;

final class RemoteMediaValueConverterTest extends TestCase
{
    private RemoteMediaValueConverter $valueConverter;

    protected function setUp(): void
    {
        $this->valueConverter = new RemoteMediaValueConverter();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::supports
     */
    public function testSupports(): void
    {
        self::assertTrue(
            $this->valueConverter->supports(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: '82af8fbba84fe6a67fce84584a4c72dd',
                    ),
                ),
            ),
        );

        self::assertFalse($this->valueConverter->supports(new stdClass()));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getValueType
     */
    public function testGetValueType(): void
    {
        self::assertSame(
            'remote_media',
            $this->valueConverter->getValueType(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: 'b25af57a577fe6b6f65f1e0f70c69003',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getId
     */
    public function testGetId(): void
    {
        self::assertSame(
            'upload|image|folder/test_resource',
            $this->valueConverter->getId(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: 'f1f57cad4a9a2f914ae50b11210e9f12',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getRemoteId
     */
    public function testGetRemoteId(): void
    {
        self::assertSame(
            'upload|image|folder/test_resource',
            $this->valueConverter->getRemoteId(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: '11057631351990d934c4d524177ad4fc',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getName
     */
    public function testGetName(): void
    {
        self::assertSame(
            'test_resource',
            $this->valueConverter->getName(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: '42a24da20d5d70df13ea472a459ef865',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getIsVisible
     */
    public function testGetIsVisible(): void
    {
        self::assertTrue(
            $this->valueConverter->getIsVisible(
                new RemoteResourceLocation(
                    new RemoteResource(
                        remoteId: 'upload|image|folder/test_resource',
                        type: RemoteResource::TYPE_IMAGE,
                        url: 'https://cloudinary.com/test/upload/folder/test_resource',
                        md5: '88be2ccddf0d9841480b40c9ca599b37',
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueConverter\RemoteMediaValueConverter::getObject
     */
    public function testGetObject(): void
    {
        $object = new RemoteResourceLocation(
            new RemoteResource(
                remoteId: 'upload|image|folder/test_resource',
                type: RemoteResource::TYPE_IMAGE,
                url: 'https://cloudinary.com/test/upload/folder/test_resource',
                md5: '6be43c2f48d23227c0e2224c54f7fb40',
            ),
        );

        self::assertSame($object, $this->valueConverter->getObject($object));
    }
}
