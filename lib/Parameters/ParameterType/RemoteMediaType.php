<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia as RemoteMediaConstraint;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Parameter type used to store and validate an ID and type of resource in RemoteMedia.
 */
final class RemoteMediaType extends ParameterType
{
    public static function getIdentifier(): string
    {
        return 'remote_media';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefault('allowed_types', []);

        parent::configureOptions($optionsResolver);
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, $value): array
    {
        return [
            new Constraints\Type(['type' => 'string']),
            new RemoteMediaConstraint(),
        ];
    }
}
