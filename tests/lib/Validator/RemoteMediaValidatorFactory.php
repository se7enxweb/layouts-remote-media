<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\Validator;

use Netgen\Layouts\RemoteMedia\Validator\RemoteMediaValidator;
use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

final class RemoteMediaValidatorFactory implements ConstraintValidatorFactoryInterface
{
    private ProviderInterface $provider;

    private ConstraintValidatorFactory $baseValidatorFactory;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
        $this->baseValidatorFactory = new ConstraintValidatorFactory();
    }

    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $name = $constraint->validatedBy();

        if ($name === 'netgen_remote_media') {
            return new RemoteMediaValidator($this->provider);
        }

        return $this->baseValidatorFactory->getInstance($constraint);
    }
}
