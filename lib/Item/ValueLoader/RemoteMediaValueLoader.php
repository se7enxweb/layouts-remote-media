<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

final class RemoteMediaValueLoader implements ValueLoaderInterface
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function load($id): ?object
    {
        $query = ResourceQuery::createFromString((string) $id);

        try {
            return $this->provider->loadByRemoteId($query->getRemoteId());
        } catch (RemoteResourceNotFoundException $e) {
            return null;
        }
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }
}
