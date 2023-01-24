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
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

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
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

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
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|video|folder/test_resource',
            'url' => 'https://cloudinary.com/test/upload/video/folder/test_resource',
            'folder' => Folder::fromPath('folder'),
            'name' => 'test_resource',
        ]);

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
        $resource = new RemoteResource([
            'type' => RemoteResource::TYPE_VIDEO,
            'remoteId' => 'upload|image|folder/example',
            'url' => 'https://cloudinary.com/test/upload/image/folder/example',
            'folder' => Folder::fromPath('folder'),
            'name' => 'example',
        ]);

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
