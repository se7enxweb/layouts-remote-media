<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Item\ValueLoader;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Netgen\Layouts\Item\ValueLoaderInterface;
use Netgen\Layouts\RemoteMedia\API\Values\LayoutsRemoteResource;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

final class RemoteMediaValueLoader implements ValueLoaderInterface
{
    private ProviderInterface $provider;

    private EntityManagerInterface $entityManager;

    private EntityRepository $layoutsRemoteResourceRepository;

    public function __construct(ProviderInterface $provider, EntityManagerInterface $entityManager)
    {
        $this->provider = $provider;
        $this->entityManager = $entityManager;
        $this->layoutsRemoteResourceRepository = $entityManager->getRepository(LayoutsRemoteResource::class);
    }

    public function load($id): ?object
    {
        $query = ResourceQuery::createFromValue((string) $id);

        $layoutsRemoteResource = $this->layoutsRemoteResourceRepository->findOneBy(['value' => $query->getValue()]);

        if (!$layoutsRemoteResource instanceof LayoutsRemoteResource) {
            try {
                $remoteResource = $this->resolveRemoteResource($query);
                $remoteResourceLocation = new RemoteResourceLocation($remoteResource);

                $this->provider->store($remoteResource);
                $remoteResourceLocation = $this->provider->storeLocation($remoteResourceLocation);
            } catch (RemoteResourceNotFoundException $e) {
                return null;
            }

            $layoutsRemoteResource = new LayoutsRemoteResource($query->getValue(), $remoteResourceLocation);

            $this->entityManager->persist($layoutsRemoteResource);
            $this->entityManager->flush();
        }

        return $layoutsRemoteResource->getRemoteResourceLocation();
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return $this->load($remoteId);
    }

    private function resolveRemoteResource(ResourceQuery $query): RemoteResource
    {
        try {
            return $this->provider->loadByRemoteId($query->getRemoteId());
        } catch (RemoteResourceNotFoundException $e) {
            return $this->provider->loadFromRemote($query->getRemoteId());
        }
    }
}
