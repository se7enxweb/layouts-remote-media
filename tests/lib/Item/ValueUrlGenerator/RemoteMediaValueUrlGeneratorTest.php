<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Item\ValueUrlGenerator;

use Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use PHPUnit\Framework\TestCase;

final class RemoteMediaValueUrlGeneratorTest extends TestCase
{
    private RemoteMediaValueUrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = new RemoteMediaValueUrlGenerator();
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator\RemoteMediaValueUrlGenerator::generate
     */
    public function testGenerate(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '25bd63baf9c2509af5c2d33a65da98ce',
        );

        $location = new RemoteResourceLocation($resource);

        self::assertSame('https://cloudinary.com/test/upload/video/folder/test_resource', $this->urlGenerator->generate($location));
    }
}
