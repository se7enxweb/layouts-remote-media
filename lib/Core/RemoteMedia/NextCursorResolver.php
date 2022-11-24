<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use Netgen\RemoteMedia\API\Search\Query;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use function implode;
use function str_replace;
use function trim;

final class NextCursorResolver
{
    public const PROJECT_KEY = 'layoutsremotemedia';
    public const PROVIDER_KEY = 'cloudinary';
    public const NEXT_CURSOR = 'nextcursor';

    private CacheItemPoolInterface $cache;

    private int $ttl;

    public function __construct(CacheItemPoolInterface $cache, int $ttl = 7200)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function resolve(Query $query, int $offset): string
    {
        $cacheKey = $this->getCacheKey($query, $offset);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        throw new RuntimeException("Can't get cursor key for query: " . $query . " with offset: {$offset}");
    }

    public function save(Query $query, int $offset, string $cursor): void
    {
        $cacheKey = $this->getCacheKey($query, $offset);
        $cacheItem = $this->cache->getItem($cacheKey);

        $cacheItem->set($cursor);
        $cacheItem->expiresAfter($this->ttl);

        $this->cache->save($cacheItem);
    }

    private function getCacheKey(Query $query, int $offset): string
    {
        return $this->washKey(
            implode('-', [self::PROJECT_KEY, self::PROVIDER_KEY, self::NEXT_CURSOR, (string) $query, $offset]),
        );
    }

    private function washKey(string $key): string
    {
        $forbiddenCharacters = ['{', '}', '(', ')', '/', '\\', '@'];
        foreach ($forbiddenCharacters as $char) {
            $key = str_replace($char, '_', trim($key, $char));
        }

        return $key;
    }
}
