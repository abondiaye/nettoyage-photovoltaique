<?php

namespace App\Entity;

use App\Repository\ReservationHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: ReservationHistoryRepository::class)]
#[ORM\Table(name: 'reservation_history')]
class ReservationHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateAction = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTime $heureAction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $administrateur = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $ancienneValeur = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $nouvelleValeur = null;

    public function __construct()
    {
        $this->dateAction = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;
        return $this;
    }

    public function getDateAction(): ?\DateTime
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTime $dateAction): static
    {
        $this->dateAction = $dateAction;
        return $this;
    }

    public function getHeureAction(): ?\DateTime
    {
        return $this->heureAction;
    }

    public function setHeureAction(\DateTime $heureAction): static
    {
        $this->heureAction = $heureAction;
        return $this;
    }

    public function getAdministrateur(): ?string
    {
        return $this->administrateur;
    }

    public function setAdministrateur(?string $administrateur): static
    {
        $this->administrateur = $administrateur;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getAncienneValeur(): ?string
    {
        return $this->ancienneValeur;
    }

    public function setAncienneValeur(?string $ancienneValeur): static
    {
        $this->ancienneValeur = $ancienneValeur;
        return $this;
    }

    public function getNouvelleValeur(): ?string
    {
        return $this->nouvelleValeur;
    }

    public function setNouvelleValeur(?string $nouvelleValeur): static
    {
        $this->nouvelleValeur = $nouvelleValeur;
        return $this;
    }
}
