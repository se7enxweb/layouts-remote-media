<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Block\BlockDefinition\Handler;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType\ChoiceType;
use Netgen\Layouts\Parameters\ParameterType\TextLineType;
use Netgen\Layouts\RemoteMedia\Parameters\ParameterType\RemoteMediaType;
use Netgen\RemoteMedia\Core\Resolver\Variation as VariationResolver;

final class RemoteMediaHandler extends BlockDefinitionHandler
{
    private const LAYOUTS_BLOCK_VARIATIONS = 'netgen_layouts_block';

    private ValueLoaderInterface $valueLoader;

    private VariationResolver $variationResolver;

    /**
     * @var string[]
     */
    private array $allowedResourceTypes;

    /**
     * @param string[] $allowedResourceTypes
     */
    public function __construct(
        ValueLoaderInterface $valueLoader,
        VariationResolver $variationResolver,
        array $allowedResourceTypes
    ) {
        $this->valueLoader = $valueLoader;
        $this->variationResolver = $variationResolver;
        $this->allowedResourceTypes = $allowedResourceTypes;
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'remote_media',
            RemoteMediaType::class,
            [
                'required' => false,
                'allowed_types' => $this->allowedResourceTypes,
            ],
        );

        $builder->add(
            'variation',
            ChoiceType::class,
            [
                'required' => false,
                'options' => $this->getVariationOptions(),
            ],
        );

        $builder->add(
            'title',
            TextLineType::class,
        );
    }

    public function getDynamicParameters(DynamicParameters $params, Block $block): void
    {
        $params['resource'] = null;

        if ($block->getParameter('remote_media')->isEmpty()) {
            return;
        }

        $remoteMediaId = $block->getParameter('remote_media')->getValue();
        $params['resource'] = $this->valueLoader->load($remoteMediaId);
    }

    /**
     * @return array<string, string|null>
     */
    private function getVariationOptions(): array
    {
        $options = [
            '(no variation)' => null,
        ];

        $variations = $this->variationResolver->getAvailableVariations(self::LAYOUTS_BLOCK_VARIATIONS);

        foreach ($variations as $key => $value) {
            $options[(string) $key] = (string) $key;
        }

        return $options;
    }
}
