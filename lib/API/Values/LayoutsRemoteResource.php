<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\API\Values;

use Doctrine\ORM\Mapping as ORM;
use Netgen\RemoteMedia\API\Values\RemoteResourceLocation;
use Netgen\RemoteMedia\API\Values\TimestampableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="ngl_remote_media_resource")
 * @ORM\HasLifecycleCallbacks()
 */
class LayoutsRemoteResource
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="remote_id", unique=true, type="string", length=255)
     */
    private string $value;

    /**
     * @ORM\OneToOne(targetEntity="Netgen\RemoteMedia\API\Values\RemoteResourceLocation")
     * @ORM\JoinColumn(name="remote_resource_location_id", referencedColumnName="id")
     */
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
