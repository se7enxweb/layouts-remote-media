<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Tests\ContentBrowser\Backend;

use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Item;
use Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia\Location;
use Netgen\Layouts\RemoteMedia\Core\RemoteMedia\NextCursorResolverInterface;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Search\Query;
use Netgen\RemoteMedia\API\Search\Result;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;

final class RemoteMediaBackendTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\RemoteMedia\API\ProviderInterface
     */
    private MockObject $providerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Netgen\Layouts\RemoteMedia\Core\RemoteMedia\NextCursorResolverInterface
     */
    private MockObject $nextCursorResolverMock;

    /**
     * Mocked Translator class directly due to supporting Symfony versions from 3 to 5
     * (TranslationInterface has been deprecated in v4 and replaced in v5 with TranslatorInterface
     * from symfony/translation-contracts bundle).
     *
     * @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\Translation\Translator
     */
    private MockObject $translatorMock;

    private Configuration $config;

    private RemoteMediaBackend $backend;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->nextCursorResolverMock = $this->createMock(NextCursorResolverInterface::class);
        $this->translatorMock = $this->createMock(Translator::class);
        $this->config = new Configuration('remote_media', 'Remote media', []);

        $this->backend = new RemoteMediaBackend(
            $this->providerMock,
            $this->nextCursorResolverMock,
            $this->translatorMock,
            $this->config,
        );
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSections(): void
    {
        $this->translatorMock
            ->expects(self::exactly(6))
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.audio', [], 'ngcb', null, 'Audio'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.document', [], 'ngcb', null, 'Document'],
                    ['backend.remote_media.resource_type.other', [], 'ngcb', null, 'Other'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(6, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSectionsWithFilter(): void
    {
        $this->config->setParameter('allowed_types', 'image,video');

        $this->translatorMock
            ->expects(self::exactly(3))
            ->method('trans')
            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(3, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::__construct
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::buildSections
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSections
     */
    public function testGetSectionsWithEmptyFilter(): void
    {
        $this->config->setParameter('allowed_types', '');

        $this->translatorMock
            ->expects(self::exactly(6))
            ->method('trans')

            ->willReturnMap(
                [
                    ['backend.remote_media.resource_type.all', [], 'ngcb', null, 'All resources'],
                    ['backend.remote_media.resource_type.image', [], 'ngcb', null, 'Image'],
                    ['backend.remote_media.resource_type.audio', [], 'ngcb', null, 'Audio'],
                    ['backend.remote_media.resource_type.video', [], 'ngcb', null, 'Video'],
                    ['backend.remote_media.resource_type.document', [], 'ngcb', null, 'Document'],
                    ['backend.remote_media.resource_type.other', [], 'ngcb', null, 'Other'],
                ],
            );

        $sections = $this->backend->getSections();

        self::assertCount(6, $sections);
        self::assertContainsOnlyInstancesOf(Location::class, $sections);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadLocation
     */
    public function testLoadLocation(): void
    {
        $location = $this->backend->loadLocation('video||media|videos');

        self::assertSame('video||media|videos', $location->getLocationId());
        self::assertSame('videos', $location->getName());
        self::assertSame('video||media', $location->getParentId());

        $location = $this->backend->loadLocation('video||media');

        self::assertSame('video||media', $location->getLocationId());
        self::assertSame('media', $location->getName());
        self::assertSame('video', $location->getParentId());

        $location = $this->backend->loadLocation('video');

        self::assertSame('video', $location->getLocationId());
        self::assertSame('video', $location->getName());
        self::assertNull($location->getParentId());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadItem
     */
    public function testLoadItem(): void
    {
        $value = 'upload||video||media|videos|my_video.mp4';
        $resource = new RemoteResource(
            remoteId: 'upload|video|media/videos/my_video.mp4',
            type: RemoteResource::TYPE_VIDEO,
            url: 'https://cloudinary.com/test/upload/video/media/videos/my_video.mp4',
            md5: 'd4e74f7778d6c5a65f8066593e06a93d',
            name: 'my_video.mp4',
            folder: Folder::fromPath('media/videos'),
        );

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|video|media/videos/my_video.mp4')
            ->willReturn($resource);

        $item = $this->backend->loadItem($value);

        self::assertInstanceOf(Item::class, $item);
        self::assertSame($value, $item->getValue());
        self::assertSame('my_video.mp4', $item->getName());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::loadItem
     */
    public function testLoadItemNotFound(): void
    {
        $value = 'upload||video||media|videos|my_video.mp4';

        $this->providerMock
            ->expects(self::once())
            ->method('loadFromRemote')
            ->with('upload|video|media/videos/my_video.mp4')
            ->willThrowException(
                new RemoteResourceNotFoundException('upload|video|media/videos/my_video.mp4'),
            );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Remote media with ID "' . $value . '" not found.');

        $this->backend->loadItem($value);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocations
     */
    public function testGetSubLocationsRoot(): void
    {
        $location = Location::createAsSection('other', 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocations
     */
    public function testGetSubLocationsFolder(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('test_folder/test_subfolder'), 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->with(Folder::fromPath('test_folder/test_subfolder'))
            ->willReturn($folders);

        $locations = $this->backend->getSubLocations($location);

        self::assertCount(3, $locations);
        self::assertContainsOnlyInstancesOf(Location::class, $locations);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocations
     */
    public function testGetSubLocationsInvalidLocation(): void
    {
        $locationMock = $this->createMock(LocationInterface::class);

        self::assertSame([], $this->backend->getSubLocations($locationMock));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountRoot(): void
    {
        $location = Location::createAsSection('other', 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->willReturn($folders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountFolder(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('test_folder/test_subfolder'), 'other');

        $folders = [
            Folder::fromPath('downloads'),
            Folder::fromPath('files'),
            Folder::fromPath('documents'),
        ];

        $this->providerMock
            ->expects(self::once())
            ->method('listFolders')
            ->with(Folder::fromPath('test_folder/test_subfolder'))
            ->willReturn($folders);

        self::assertSame(3, $this->backend->getSubLocationsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubLocationsCount
     */
    public function testGetSubLocationsCountInvalidLocation(): void
    {
        $locationMock = $this->createMock(LocationInterface::class);

        self::assertSame(0, $this->backend->getSubLocationsCount($locationMock));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItems(): void
    {
        $location = Location::createAsSection('image', 'Image');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            types: ['image'],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $items = $this->backend->getSubItems($location);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithOffset(): void
    {
        $location = Location::createFromId('all||media|new');
        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            folders: [Folder::fromPath('media/new')],
            limit: 5,
        );

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            folders: [Folder::fromPath('media/new')],
            limit: 5,
            nextCursor: $nextCursor,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 10, 'testcursor123');

        $items = $this->backend->getSubItems($location, 5, 5);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithFilter(): void
    {
        $location = Location::createFromId('all||media|latest');

        $this->config->setParameter('allowed_types', 'image,other');

        $query = new Query(
            types: ['image', 'other'],
            folders: [Folder::fromPath('media/latest')],
            limit: 5,
        );

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 5, 'testcursor123');

        $items = $this->backend->getSubItems($location, 0, 5);

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            types: ['video'],
            limit: 25,
        );

        $searchResult = $this->getEmptySearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('save');

        self::assertSame([], $this->backend->getSubItems($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItems
     */
    public function testGetSubItemsInvalidLocation(): void
    {
        $locationMock = $this->createMock(LocationInterface::class);

        self::assertSame([], $this->backend->getSubItems($locationMock));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountInSection(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $query = new Query(
            types: ['video'],
            limit: 0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(150);

        self::assertSame(150, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCount(): void
    {
        $location = Location::createAsSection('all', 'All');

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountInFolderWithFilter(): void
    {
        $location = Location::createFromId('all||media|latest|blog');

        $this->config->setParameter('allowed_types', 'image');

        $query = new Query(
            types: ['image'],
            folders: [Folder::fromPath('media/latest/blog')],
            limit: 0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(6000);

        self::assertSame(6000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountWithEmptyFilter(): void
    {
        $location = Location::createAsSection('all', 'All');

        $this->config->setParameter('allowed_types', '');

        $query = new Query(
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 0,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(1000);

        self::assertSame(1000, $this->backend->getSubItemsCount($location));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getSubItemsCount
     */
    public function testGetSubItemsCountInvalidLocation(): void
    {
        $locationMock = $this->createMock(LocationInterface::class);

        self::assertSame(0, $this->backend->getSubItemsCount($locationMock));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItems(): void
    {
        $location = Location::createFromId('all');

        $searchQuery = new SearchQuery('test', $location);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithFilter(): void
    {
        $location = Location::createFromFolder(Folder::fromPath('media'), 'all');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'other');

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            query: 'test',
            types: ['other'],
            folders: [Folder::fromPath('media')],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithOffset(): void
    {
        $location = Location::createFromId('image');

        $searchQuery = new SearchQuery('test', $location);
        $searchQuery->setLimit(5);
        $searchQuery->setOffset(5);

        $nextCursor = 'k83hn24hs92ao98';

        $query = new Query(
            query: 'test',
            types: ['image'],
            limit: 5,
        );

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('resolve')
            ->with($query, 5)
            ->willReturn($nextCursor);

        $query = new Query(
            query: 'test',
            types: ['image'],
            limit: 5,
            nextCursor: $nextCursor,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 10, 'testcursor123');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertCount(5, $searchResult->getResults());
        self::assertContainsOnlyInstancesOf(Item::class, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItems
     */
    public function testSearchItemsWithNoResults(): void
    {
        $location = Location::createAsSection('video', 'Video');

        $searchQuery = new SearchQuery('non-existing text', $location);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            query: 'non-existing text',
            types: ['video'],
            limit: 25,
        );

        $searchResult = $this->getEmptySearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('save');

        $searchResult = $this->backend->searchItems($searchQuery);

        self::assertInstanceOf(SearchResult::class, $searchResult);
        self::assertCount(0, $searchResult->getResults());
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCount(): void
    {
        $location = Location::createFromId('other||media|files');

        $searchQuery = new SearchQuery('test', $location);

        $query = new Query(
            query: 'test',
            types: ['other'],
            folders: [Folder::fromPath('media/files')],
            limit: 25,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::getAllowedTypes
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCountWithFilter(): void
    {
        $location = Location::createFromId('all||media|files');

        $searchQuery = new SearchQuery('test', $location);

        $this->config->setParameter('allowed_types', 'video');

        $query = new Query(
            query: 'test',
            types: ['video'],
            folders: [Folder::fromPath('media/files')],
            limit: 25,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchItemsCount
     */
    public function testSearchItemsCountWithoutLocation(): void
    {
        $searchQuery = new SearchQuery('test');

        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(12);

        self::assertSame(12, $this->backend->searchItemsCount($searchQuery));
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::search
     */
    public function testSearch(): void
    {
        $this->nextCursorResolverMock
            ->expects(self::never())
            ->method('resolve');

        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $searchResult = $this->getSearchResult();

        $this->providerMock
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn($searchResult);

        $this->nextCursorResolverMock
            ->expects(self::once())
            ->method('save')
            ->with($query, 25, 'testcursor123');

        $items = $this->backend->search('test');

        self::assertCount(5, $items);
        self::assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * @covers \Netgen\Layouts\RemoteMedia\ContentBrowser\Backend\RemoteMediaBackend::searchCount
     */
    public function testSearchCount(): void
    {
        $query = new Query(
            query: 'test',
            types: ['image', 'audio', 'video', 'document', 'other'],
            limit: 25,
        );

        $this->providerMock
            ->expects(self::once())
            ->method('searchCount')
            ->with($query)
            ->willReturn(15);

        self::assertSame(15, $this->backend->searchCount('test'));
    }

    private function getSearchResult(): Result
    {
        return new Result(
            15,
            'testcursor123',
            [
                $this->getResource('test_resource_1', RemoteResource::TYPE_IMAGE, 'https://cloudinary.com/test/upload/image/test_resource_1', '857bcccd18b32a4463760bffd77d87f6'),
                $this->getResource('test_resource_2', RemoteResource::TYPE_VIDEO, 'https://cloudinary.com/test/upload/video/test_resource_2', '83c98c7ec6a1d2ef4b609892ffb17f3e'),
                $this->getResource('test_resource_3', RemoteResource::TYPE_AUDIO, 'https://cloudinary.com/test/upload/audio/test_resource_3', '495219081e3353c31ef3e149f99b04fe'),
                $this->getResource('test_resource_4', RemoteResource::TYPE_DOCUMENT, 'https://cloudinary.com/test/upload/document/test_resource_4', 'd44f50df3af3a8e497269859c77acedf'),
                $this->getResource('folder/test_resource_5', RemoteResource::TYPE_OTHER, 'https://cloudinary.com/test/upload/raw/test_resource_5', '955d612b460288731a497557b6f4ffb0'),
            ],
        );
    }

    private function getEmptySearchResult(): Result
    {
        return new Result(0, null, []);
    }

    private function getResource(string $remoteId, string $type, string $url, string $md5): RemoteResource
    {
        return new RemoteResource(
            remoteId: $remoteId,
            type: $type,
            url: $url,
            md5: $md5,
        );
    }
}
