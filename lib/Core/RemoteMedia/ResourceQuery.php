<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use function array_shift;
use function explode;
use function implode;

final class ResourceQuery
{
    private string $resourceId;

    private string $resourceType;

    private function __construct(string $resourceId, string $resourceType)
    {
        $this->resourceId = $resourceId;
        $this->resourceType = $resourceType;
    }

    public static function createFromString(string $input): self
    {
        $parts = explode('|', $input);
        $resourceType = array_shift($parts);
        $resourceId = implode('/', $parts);

        return new self($resourceId, $resourceType);
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getType(): string
    {
        return $this->resourceType;
    }
}
