<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueConverter;

use Netgen\Layouts\Item\ValueConverterInterface;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;

use function array_pop;
use function explode;

/**
 * @implements \Netgen\Layouts\Item\ValueConverterInterface<\Netgen\RemoteMedia\API\Values\RemoteResourceLocation>
 */
final class RemoteMediaValueConverter implements ValueConverterInterface
{
    public function supports(object $object): bool
    {
        return $object instanceof RemoteResourceLocation;
    }

    public function getValueType(object $object): string
    {
        return 'remote_media';
    }

    public function getId(object $object)
    {
        return $object->getRemoteResource()->getRemoteId();
    }

    public function getRemoteId(object $object)
    {
        return $object->getRemoteResource()->getRemoteId();
    }

    public function getName(object $object): string
    {
        $parts = explode('/', $object->getRemoteResource()->getRemoteId());

        return array_pop($parts);
    }

    public function getIsVisible(object $object): bool
    {
        return true;
    }

    public function getObject(object $object): RemoteResourceLocation
    {
        return $object;
    }
}
