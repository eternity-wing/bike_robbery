<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BikeRepository")
 * @UniqueEntity("licenseNumber")
 * @JMS\ExclusionPolicy("all")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "api_v1_bikes_show",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 * @Hateoas\Relation(
 *      "edit",
 *      href = @Hateoas\Route(
 *          "api_v1_bikes_edit",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "api_v1_bikes_delete",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 *
 * @Hateoas\Relation(
 *      "resolve",
 *      href = @Hateoas\Route(
 *          "api_v1_bikes_resolve",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 *
 */
class Bike
{
    const AVAILABLE_COLORS = ['red', 'green', 'blue', 'black', 'white', 'multi-color'];
    const AVAILABLE_TYPES = ['sport', 'road', 'speed', 'mountain', 'hybrid', 'folding'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @JMS\Expose()
     */
    private $licenseNumber;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $ownerFullName;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $stealingDate;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @JMS\Expose()
     */
    private $stealingDescription;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     * @JMS\Expose()
     */
    private $isResolved=false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Police", inversedBy="bikes")
     * @JMS\Expose()
     */
    private $responsible;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOwnerFullName(): ?string
    {
        return $this->ownerFullName;
    }

    public function setOwnerFullName(string $ownerFullName): self
    {
        $this->ownerFullName = $ownerFullName;

        return $this;
    }

    public function getStealingDate(): ?\DateTimeInterface
    {
        return $this->stealingDate;
    }

    public function setStealingDate(\DateTimeInterface $stealingDate): self
    {
        $this->stealingDate = $stealingDate;

        return $this;
    }

    public function getStealingDescription(): ?string
    {
        return $this->stealingDescription;
    }

    public function setStealingDescription(string $stealingDescription): self
    {
        $this->stealingDescription = $stealingDescription;

        return $this;
    }

    public function getIsResolved(): ?bool
    {
        return $this->isResolved;
    }

    public function setIsResolved(bool $isResolved): self
    {
        $this->isResolved = $isResolved;

        return $this;
    }

    public function getResponsible(): ?Police
    {
        return $this->responsible;
    }

    public function setResponsible(?Police $responsible): self
    {
        $this->responsible = $responsible;

        return $this;
    }
}
