<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use function array_map;
use function explode;
use function implode;
use function str_replace;

final class ResourceQuery
{
    private function __construct(
        private string $value
    ) {
    }

    public static function createFromValue(string $value): self
    {
        return new self($value);
    }

    public static function createFromRemoteId(string $remoteId): self
    {
        $value = str_replace(['|', '/'], ['||', '|'], $remoteId);

        return new self($value);
    }

    public function getRemoteId(): string
    {
        $parts = array_map(
            static fn ($part) => str_replace('|', '/', $part),
            explode('||', $this->value),
        );

        return implode('|', $parts);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
