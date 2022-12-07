<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use Netgen\RemoteMedia\API\Search\Query;

interface NextCursorResolverInterface
{
    public const PROJECT_KEY = 'layoutsremotemedia';
    public const PROVIDER_KEY = 'cloudinary';
    public const NEXT_CURSOR = 'nextcursor';

    public function resolve(Query $query, int $offset): string;

    public function save(Query $query, int $offset, string $cursor);
}
