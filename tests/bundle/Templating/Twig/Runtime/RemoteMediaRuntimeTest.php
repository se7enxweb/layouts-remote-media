<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsRemoteMediaBundle\Tests\Templating\Twig\Runtime;

use Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\RemoteResourceVariation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoteMediaRuntimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\API\ProviderInterface
     */
    private MockObject $providerMock;

    private RemoteMediaRuntime $runtime;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $this->runtime = new RemoteMediaRuntime($this->providerMock);
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getBlockVariation
     */
    public function testGetBlockVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '8b897d88656a28c2e18d01eff609714a',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_resource';
        $variation = new RemoteResourceVariation($resource, $variationUrl);

        $location = new RemoteResourceLocation($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($location, 'netgen_layouts_block', 'test_variation')
            ->willReturn($variation);

        self::assertSame(
            $variationUrl,
            $this->runtime->getBlockVariation($location, 'test_variation')->getUrl(),
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getItemVariation
     */
    public function testGetItemVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: 'ded2390a128a3b53eaffea9eeffd7ebf',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $variationUrl = 'https://cloudinary.com/upload/some_variation_config/test_resource';
        $variation = new RemoteResourceVariation($resource, $variationUrl);

        $location = new RemoteResourceLocation($resource);

        $this->providerMock
            ->expects(self::once())
            ->method('buildVariation')
            ->with($location, 'netgen_layouts_item', 'test_variation')
            ->willReturn($variation);

        self::assertSame(
            $variationUrl,
            $this->runtime->getItemVariation($location, 'test_variation')->getUrl(),
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getBlockTag
     */
    public function testGetBlockTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: 'ad4ddb016c2e1de6dc44d6d63c3a29c9',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource);
        $tagString = '<video src="https://cloudinary.com/upload/test_resource">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateHtmlTag')
            ->with($resource, [], true, false)
            ->willReturn($tagString);

        self::assertSame(
            $tagString,
            $this->runtime->getBlockTag($location),
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getBlockTag
     */
    public function testGetBlockTagWithVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|video|folder/test_resource',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/folder/test_resource',
            md5: '4b0b61fe5dc9d44178d4814e170fb22c',
            name: 'test_resource',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource);
        $tagString = '<video src="https://cloudinary.com/upload/some_variation_config/test_resource">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVariationHtmlTag')
            ->with($location, 'netgen_layouts_block', 'test_variation', [], true, false)
            ->willReturn($tagString);

        self::assertSame(
            $tagString,
            $this->runtime->getBlockTag($location, 'test_variation'),
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getItemTag
     */
    public function testGetItemTag(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|folder/example',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/image/folder/example',
            md5: '696fee858c6493ea465935ad3202e0ba',
            name: 'example',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource);
        $tagString = '<img src="https://cloudinary.com/upload/some_variation_config/example">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateHtmlTag')
            ->with($resource, [], true, true)
            ->willReturn($tagString);

        self::assertSame(
            $tagString,
            $this->runtime->getItemTag($location, null, true),
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::__construct
     * @covers \Netgen\Bundle\LayoutsRemoteMediaBundle\Templating\Twig\Runtime\RemoteMediaRuntime::getItemTag
     */
    public function testGetItemTagWithVariation(): void
    {
        $resource = new RemoteResource(
            remoteId: 'upload|image|folder/example',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/image/folder/example',
            md5: '0cd429efe5e3a40b49d4c3faac1ceb24',
            name: 'example',
            folder: Folder::fromPath('folder'),
        );

        $location = new RemoteResourceLocation($resource);
        $tagString = '<img src="https://cloudinary.com/upload/some_variation_config/example">';

        $this->providerMock
            ->expects(self::once())
            ->method('generateVariationHtmlTag')
            ->with($location, 'netgen_layouts_item', 'test_variation', [], true, true)
            ->willReturn($tagString);

        self::assertSame(
            $tagString,
            $this->runtime->getItemTag($location, 'test_variation', true),
        );
    }
}
