<?php

namespace App\Entity;

use App\Repository\CarsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;
use Doctrine\ORM\EntityManagerInterface;
#[ORM\Entity(repositoryClass: CarsRepository::class)]
class Cars
{
  

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $km = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $available = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: "owner_id", referencedColumnName: "id")]
    private ?Users $owner_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(int $km): static
    {
        $this->km = $km;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): static
    {
        $this->available = $available;

        return $this;
    }

    public function getOwnerId(): ?Users
    {
        return $this->owner_id;
    }

    public function setOwnerId(Users $owner_id): static
    {
        $this->owner_id = $owner_id;

        return $this;
    }


    public function getUnavailableDates(EntityManagerInterface $entityManager ): array
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
            ->from('App\Entity\Commands', 'c')
            ->where('c.car_id = :car_id')
            ->setParameter('car_id', $this->getId());

        $commands = $qb->getQuery()->getResult();

        $unavailableDates = [];
        foreach ($commands as $command) {
            $unavailableDates[] = $command->getStartDate()->format('Y-m-d');
            $unavailableDates[] = $command->getEndDate()->format('Y-m-d');
        }

        return $unavailableDates;
    }



}
