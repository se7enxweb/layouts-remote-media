<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Core\RemoteMedia;

use Netgen\RemoteMedia\API\Values\Folder;
use function array_shift;
use function explode;

final class ResourceQuery
{
    private string $remoteId;

    private string $type;

    private ?Folder $folder;

    private function __construct(string $remoteId, string $type, ?Folder $folder = null)
    {
        $this->remoteId = $remoteId;
        $this->type = $type;
        $this->folder = $folder;
    }

    public static function createFromString(string $input): self
    {
        $parts = explode('|', $input);
        $type = array_shift($parts);
        $folder = urldecode(array_shift($parts));
        $remoteId = urldecode(array_shift($parts));

        $folder = $folder !== '' ? Folder::fromPath($folder) : null;

        return new self($remoteId, $type, $folder);
    }

    public function getRemoteId(): string
    {
        return $this->remoteId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }
}
