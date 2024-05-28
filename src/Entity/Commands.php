<?php

namespace App\Entity;

use App\Repository\CommandsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

#[ORM\Entity(repositoryClass: CommandsRepository::class)]
class Commands
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cars::class)]
    #[ORM\JoinColumn(name: "car_id", referencedColumnName: "id")]
    private ?Cars $car_id;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private ?Users $user_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $rental_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column]
    private ?int $rental_period = null;

    #[ORM\Column(nullable: true)]
    private ?bool $confirmed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCarId(): ?Cars
    {
        return $this->car_id;
    }

    public function setCarId(Cars $car_id): static
    {
        $this->car_id = $car_id;

        return $this;
    }

    public function getUserId(): ?Users
    {
        return $this->user_id;
    }

    public function setUserId(Users $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getRentalDate(): ?\DateTimeInterface
    {
        return $this->rental_date;
    }

    public function setRentalDate(\DateTimeInterface $rental_date): static
    {
        $this->rental_date = $rental_date;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getRentalPeriod(): ?int
    {
        return $this->rental_period;
    }

    public function setRentalPeriod(int $rental_period): static
    {
        $this->rental_period = $rental_period;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): static
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        if ($this->car_id && $this->rental_period) {
            $carPrice = $this->car_id->getPrice();
            $totalPrice = $carPrice * $this->rental_period;
            return $totalPrice;
        }
        return null;
    }
}
