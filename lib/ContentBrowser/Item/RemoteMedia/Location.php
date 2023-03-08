<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\ContentBrowser\Item\RemoteMedia;

use InvalidArgumentException;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\RemoteMedia\API\Values\Folder;
use Netgen\RemoteMedia\API\Values\RemoteResource;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function implode;
use function in_array;
use function str_replace;

final class Location implements LocationInterface
{
    public const RESOURCE_TYPE_ALL = 'all';

    public const SUPPORTED_TYPES = [
        RemoteResource::TYPE_IMAGE,
        RemoteResource::TYPE_AUDIO,
        RemoteResource::TYPE_VIDEO,
        RemoteResource::TYPE_DOCUMENT,
        RemoteResource::TYPE_OTHER,
    ];

    private string $id;

    private ?string $name;

    private function __construct(string $id, ?string $name = null)
    {
        $idParts = explode('||', $id);
        $type = array_shift($idParts);

        if (!in_array($type, self::SUPPORTED_TYPES, true) && $type !== self::RESOURCE_TYPE_ALL) {
            throw new InvalidArgumentException('Provided ID ' . $id . ' is invalid');
        }

        $this->id = $id;
        $this->name = $name;
    }

    public static function createFromId(string $id): self
    {
        return new self($id);
    }

    public static function createFromFolder(Folder $folder, string $type = self::RESOURCE_TYPE_ALL): self
    {
        $folders = explode('/', $folder->getPath());
        $id = $type . '||' . implode('|', $folders);

        return new self($id, $folder->getName());
    }

    public static function createAsSection(string $type, ?string $sectionName = null): self
    {
        return new self($type, $sectionName);
    }

    public function getLocationId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        $idParts = explode('||', $this->id);

        if (count($idParts) === 1) {
            return $this->id;
        }

        array_shift($idParts);
        $folderPath = array_shift($idParts);
        $pathArray = explode('|', $folderPath ?? '|');

        return array_pop($pathArray);
    }

    public function getParentId(): ?string
    {
        $folder = $this->getFolder();
        if (!$folder instanceof Folder) {
            return null;
        }

        $parent = $folder->getParent();
        if (!$parent instanceof Folder) {
            return $this->getType();
        }

        return self::createFromFolder($parent, $this->getType())->getLocationId();
    }

    public function getFolder(): ?Folder
    {
        $idParts = explode('||', $this->id);

        if (count($idParts) <= 1) {
            return null;
        }

        return Folder::fromPath(str_replace('|', '/', $idParts[1]));
    }

    public function getType(): string
    {
        $idParts = explode('||', $this->id);

        return array_shift($idParts);
    }
}
