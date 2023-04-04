<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Block\BlockDefinition\Handler;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\Parameters\Parameter;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType\ChoiceType;
use Netgen\Layouts\Parameters\ParameterType\TextLineType;
use Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;
use Netgen\RemoteMedia\Core\Transformation\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class RemoteMediaHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Layouts\Item\ValueLoaderInterface
     */
    private MockObject $valueLoaderMock;

    /**
     * @var string[]
     */
    private array $allowedResourceTypes;

    private RemoteMediaHandler $handler;

    protected function setUp(): void
    {
        $this->valueLoaderMock = $this->createMock(ValueLoaderInterface::class);

        $variationResolver = new VariationResolver(
            new Registry(),
            new NullLogger(),
            [
                'netgen_layouts_block' => [
                    'Small' => [
                        'transformations' => [
                            'limit' => [300],
                        ],
                    ],
                    'Big' => [
                        'transformations' => [
                            'limit' => [1200],
                        ],
                    ],
                ],
            ],
        );

        $this->allowedResourceTypes = ['image', 'video'];

        $this->handler = new RemoteMediaHandler(
            $this->valueLoaderMock,
            $variationResolver,
            $this->allowedResourceTypes,
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::isContextual
     */
    public function testIsContextual(): void
    {
        self::assertFalse($this->handler->isContextual(new Block()));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::buildParameters
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getVariationOptions
     */
    public function testBuildParameters(): void
    {
        $builderMock = $this->createMock(ParameterBuilderInterface::class);

        $variationOptions = [
            '(no variation)' => null,
            'Small' => 'Small',
            'Big' => 'Big',
        ];

        $builderMock
            ->expects(self::exactly(3))
            ->method('add')
            ->willReturnMap(
                [
                    ['remote_media', RemoteMediaType::class, ['required' => false, 'allowed_types' => $this->allowedResourceTypes], $builderMock],
                    ['variation', ChoiceType::class, ['required' => false, 'options' => $variationOptions], $builderMock],
                    ['title', TextLineType::class, [], $builderMock],
                ],
            );

        $this->handler->buildParameters($builderMock);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getDynamicParameters
     */
    public function testGetDynamicSettings(): void
    {
        $params = new DynamicParameters();

        $block = Block::fromArray([
            'parameters' => [
                'remote_media' => Parameter::fromArray([
                    'name' => 'remote_media',
                    'value' => 'image|folder|subfolder|image_name.jpg',
                    'isEmpty' => false,
                ]),
                'variation' => Parameter::fromArray([
                    'name' => 'variation',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'title' => Parameter::fromArray([
                    'name' => 'title',
                    'value' => 'Test title',
                    'isEmpty' => false,
                ]),
            ],
        ]);

        $value = new RemoteResource(
            remoteId: 'folder/subfolder/image_name.jpg',
            type: RemoteResource::TYPE_IMAGE,
            url: 'https://cloudinary.com/test/upload/image/folder/subfolder/image_name.jpg',
            md5: '185901e0a6f0c338cc4115a8b1923f44',
        );

        $this->valueLoaderMock
            ->expects(self::once())
            ->method('load')
            ->with('image|folder|subfolder|image_name.jpg')
            ->willReturn($value);

        $this->handler->getDynamicParameters($params, $block);

        self::assertSame($value->getRemoteId(), $params['remote_resource_location']->getRemoteId());
        self::assertSame($value->getType(), $params['remote_resource_location']->getType());
        self::assertSame($value->getUrl(), $params['remote_resource_location']->getUrl());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::__construct
     * @covers \Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler\RemoteMediaHandler::getDynamicParameters
     */
    public function testGetDynamicSettingsEmpty(): void
    {
        $params = new DynamicParameters();
        $block = Block::fromArray([
            'parameters' => [
                'remote_media' => Parameter::fromArray([
                    'name' => 'remote_media',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'variation' => Parameter::fromArray([
                    'name' => 'variation',
                    'value' => null,
                    'isEmpty' => true,
                ]),
                'title' => Parameter::fromArray([
                    'name' => 'title',
                    'value' => 'Test title',
                    'isEmpty' => false,
                ]),
            ],
        ]);

        $this->valueLoaderMock
            ->expects(self::never())
            ->method('load');

        $this->handler->getDynamicParameters($params, $block);

        self::assertNull($params['remote_resource_location']);
    }
}
