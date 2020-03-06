<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PoliceRepository")
 * @UniqueEntity("personalCode")
 * @JMS\ExclusionPolicy("all")
 */
class Police
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Expose()
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=30, unique=true)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $personalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $fullName;

    /**
     * @ORM\Column(type="boolean", options={"default": 1})
     * @JMS\Expose()
     */
    private $isAvailable=true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bike", mappedBy="responsible")
     * @JMS\Expose()
     * @JMS\Groups({"details"})
     */
    private $bikes;

    public function __construct()
    {
        $this->bikes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonalCode(): ?string
    {
        return $this->personalCode;
    }

    public function setPersonalCode(string $personalCode): self
    {
        $this->personalCode = $personalCode;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection|Bike[]
     */
    public function getBikes(): Collection
    {
        return $this->bikes;
    }

    public function addBike(Bike $bike): self
    {
        if (!$this->bikes->contains($bike)) {
            $this->bikes[] = $bike;
            $bike->setResponsible($this);
        }

        return $this;
    }

    public function removeBike(Bike $bike): self
    {
        if ($this->bikes->contains($bike)) {
            $this->bikes->removeElement($bike);
            // set the owning side to null (unless already changed)
            if ($bike->getResponsible() === $this) {
                $bike->setResponsible(null);
            }
        }

        return $this;
    }
}
