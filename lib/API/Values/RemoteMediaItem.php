<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\API\Values;

use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\TimestampableTrait;

class RemoteMediaItem
{
    use TimestampableTrait;

    private ?int $id = null;

    private string $value;

    private RemoteResourceLocation $remoteResourceLocation;

    public function __construct(string $value, RemoteResourceLocation $remoteResourceLocation)
    {
        $this->value = $value;
        $this->remoteResourceLocation = $remoteResourceLocation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getRemoteResourceLocation(): RemoteResourceLocation
    {
        return $this->remoteResourceLocation;
    }
}
