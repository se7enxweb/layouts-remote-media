<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueUrlGenerator;

use Netgen\Layouts\Item\ValueUrlGeneratorInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueUrlGeneratorInterface<\Netgen\RemoteMedia\API\Values\RemoteResource>
 */
final class RemoteMediaValueUrlGenerator implements ValueUrlGeneratorInterface
{
    public function generate(object $object): ?string
    {
        return $object->getUrl();
    }
}
