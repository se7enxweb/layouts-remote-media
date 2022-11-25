<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Validator;

use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\Layouts\RemoteMedia\Validator\Constraint\RemoteMedia;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function is_string;

final class RemoteMediaValidator extends ConstraintValidator
{
    private RemoteMediaProvider $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RemoteMedia) {
            throw new UnexpectedTypeException($constraint, RemoteMedia::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $query = ResourceQuery::createFromString($value);

        try {
            $this->provider->getRemoteResource(
                $query->getResourceId(),
                $query->getType(),
            );
        } catch (RemoteResourceNotFoundException $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%resourceId%', $query->getResourceId())
                ->setParameter('%resourceType%', $query->getType())
                ->addViolation();
        }
    }
}
