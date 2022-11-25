<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\ContentBrowser\Item\LocationInterface;

use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;
use function array_pop;
use function array_shift;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;

final class Location implements LocationInterface
{
    public const RESOURCE_TYPE_ALL = 'all';

    public const SUPPORTED_TYPES = [
        self::RESOURCE_TYPE_ALL,
        RemoteResource::TYPE_IMAGE,
        RemoteResource::TYPE_AUDIO,
        RemoteResource::TYPE_VIDEO,
        RemoteResource::TYPE_DOCUMENT,
        RemoteResource::TYPE_OTHER,
    ];

    private string $id;

    private string $name;

    private string $type;

    private ?Folder $folder;

    private ?string $parentId;

    private function __construct(
        string $id,
        string $name,
        string $type,
        ?Folder $folder = null,
        ?string $parentId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->folder = $folder;
        $this->parentId = $parentId;
    }

    public static function createFromId(string $id): self
    {
        $idParts = explode('|', $id);
        $resourceType = array_shift($idParts);

        if (!in_array($resourceType, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
        }

        $name = $resourceType;
        $folder = null;
        $parentId = null;

        if (count($idParts) > 0) {
            $folder = implode('/', $idParts);
            $name = array_pop($idParts);

            $parentId = count($idParts) > 0
                ? $resourceType . '|' . implode('|', $idParts)
                : $resourceType;
        }

        return new self($id, $name, $resourceType, Folder::fromPath($folder), $parentId);
    }

    public static function createAsSection(string $type, ?string $sectionName = null): self
    {
        if (!in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('Provided type ' . $type . ' is invalid');
        }

        return new self(
            $type,
            $sectionName ?? $type,
            $type,
        );
    }

    public static function createFromFolder(Folder $folder, string $type = self::RESOURCE_TYPE_ALL): self
    {
        $folders = explode('/', $folder->getPath());
        $id = $type . '|' . implode('|', $folders);
        $parentId = $type;

        if (count($folders) > 1) {
            $parentId .= '|' . implode('|', array_slice($folders, 0, -1));
        }

        return new self($id, $folder->getName(), $type, $folder, $parentId);
    }

    public function getLocationId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
