<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

final class RemoteMediaValueLoader implements ValueLoaderInterface
{
    private RemoteMediaProvider $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function load($id): ?object
    {
        $query = ResourceQuery::createFromString((string) $id);

        try {
            return $this->provider->getRemoteResource(
                $query->getResourceId(),
                $query->getType(),
            );
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }
}
