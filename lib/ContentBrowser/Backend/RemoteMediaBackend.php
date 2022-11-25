<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Backend;

use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\ResourceQuery;
use Netgen\RemoteMedia\API\NextCursorResolverInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\Core\RemoteMediaProvider;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;

use function count;
use function explode;
use function in_array;
use function is_string;
use function sprintf;

final class RemoteMediaBackend implements BackendInterface
{
    private RemoteMediaProvider $provider;

    private NextCursorResolverInterface $nextCursorResolver;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    private Configuration $config;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(
        RemoteMediaProvider $provider,
        NextCursorResolverInterface $nextCursorResolver,
        $translator,
        Configuration $config
    ) {
        $this->provider = $provider;
        $this->nextCursorResolver = $nextCursorResolver;
        $this->translator = $translator;
        $this->config = $config;
    }

    public function getSections(): iterable
    {
        return $this->buildSections();
    }

    public function loadLocation($id): LocationInterface
    {
        return Location::createFromId((string) $id);
    }

    public function loadItem($value): ItemInterface
    {
        $query = ResourceQuery::createFromString((string) $value);

        try {
            $resource = $this->provider->getRemoteResource(
                $query->getResourceId(),
                $query->getType(),
            );
        } catch (RemoteResourceNotFoundException $e) {
            throw new NotFoundException(
                sprintf(
                    'Remote media with ID "%s" not found.',
                    $value,
                ),
            );
        }

        return new Item($resource);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof Location) {
            return [];
        }

        $folders = $location->getFolder() !== null
            ? $this->provider->listSubFolders($location->getFolder())
            : $this->provider->listFolders();

        $locations = [];
        foreach ($folders as $folder) {
            $locations[] = Location::createFromFolder($folder['path'], $folder['name'], $location->getType());
        }

        return $locations;
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if (!$location instanceof Location) {
            return 0;
        }

        return $location->getFolder() !== null
            ? count($this->provider->listSubFolders($location->getFolder()))
            : count($this->provider->listFolders());
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof Location) {
            return [];
        }

        $resourceType = $location->getType() !== Location::RESOURCE_TYPE_ALL ?
            $location->getType()
            : $this->getAllowedTypes();

        $query = new Query(
            '',
            $resourceType,
            $limit,
            $location->getFolder(),
        );

        if ($offset > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $offset);

            $query = new Query(
                '',
                $resourceType,
                $limit,
                $location->getFolder(),
                null,
                $nextCursor,
            );
        }

        $result = $this->provider->searchResources($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $offset + $limit, $result->getNextCursor());
        }

        $items = [];
        foreach ($result->getResources() as $resource) {
            $items[] = new Item($resource);
        }

        return $items;
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof Location) {
            return 0;
        }

        $resourceType = $location->getType() !== Location::RESOURCE_TYPE_ALL ?
            $location->getType()
            : $this->getAllowedTypes();

        $query = new Query(
            '',
            $resourceType,
            0,
            $location->getFolder(),
        );

        return $this->provider->searchResourcesCount($query);
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        $resourceType = null;
        if ($searchQuery->getLocation() instanceof Location) {
            $resourceType = $searchQuery->getLocation()->getType() !== Location::RESOURCE_TYPE_ALL
                ? $searchQuery->getLocation()->getType()
                : $this->getAllowedTypes();
        }

        $query = new Query(
            $searchQuery->getSearchText(),
            $resourceType,
            $searchQuery->getLimit(),
        );

        if ($searchQuery->getOffset() > 0) {
            $nextCursor = $this->nextCursorResolver->resolve($query, $searchQuery->getOffset());

            $query = new Query(
                $searchQuery->getSearchText(),
                $resourceType,
                $searchQuery->getLimit(),
                null,
                null,
                $nextCursor,
            );
        }

        $result = $this->provider->searchResources($query);

        if (is_string($result->getNextCursor())) {
            $this->nextCursorResolver->save($query, $searchQuery->getOffset() + $searchQuery->getLimit(), $result->getNextCursor());
        }

        $items = [];
        foreach ($result->getResources() as $resource) {
            $items[] = new Item($resource);
        }

        return new SearchResult($items);
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        $resourceType = null;
        $folder = null;
        if ($searchQuery->getLocation() instanceof Location) {
            $resourceType = $searchQuery->getLocation()->getType() !== Location::RESOURCE_TYPE_ALL
                ? $searchQuery->getLocation()->getType()
                : $this->getAllowedTypes();

            $folder = $searchQuery->getLocation()->getFolder();
        }

        $query = new Query(
            $searchQuery->getSearchText(),
            $resourceType,
            $searchQuery->getLimit(),
            $folder,
        );

        return $this->provider->searchResourcesCount($query);
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        $searchQuery = new SearchQuery($searchText);
        $searchQuery->setOffset($offset);
        $searchQuery->setLimit($limit);

        $searchResult = $this->searchItems($searchQuery);

        return $searchResult->getResults();
    }

    public function searchCount(string $searchText): int
    {
        return $this->searchItemsCount(new SearchQuery($searchText));
    }

    /**
     * @return \Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location[]
     */
    private function buildSections(): array
    {
        $allowedTypes = $this->getAllowedTypes();

        $sections = [
            Location::createAsSection(
                Location::RESOURCE_TYPE_ALL,
                $this->translator->trans('backend.remote_media.resource_type.' . Location::RESOURCE_TYPE_ALL, [], 'ngcb'),
            ),
        ];

        foreach ($allowedTypes as $type) {
            $sections[] = Location::createAsSection(
                $type,
                $this->translator->trans('backend.remote_media.resource_type.' . $type, [], 'ngcb'),
            );
        }

        return $sections;
    }

    /**
     * @return string[]
     */
    private function getAllowedTypes(): array
    {
        $allowedTypes = [];

        if ($this->config->hasParameter('allowed_types')) {
            $allowedTypes = explode(',', $this->config->getParameter('allowed_types'));
        }

        $validTypes = [
            Location::RESOURCE_TYPE_IMAGE,
            Location::RESOURCE_TYPE_VIDEO,
            Location::RESOURCE_TYPE_RAW,
        ];

        foreach ($allowedTypes as $key => $type) {
            if (!in_array($type, $validTypes, true)) {
                unset($allowedTypes[$key]);
            }
        }

        return count($allowedTypes) > 0 ? $allowedTypes : $validTypes;
    }
}
