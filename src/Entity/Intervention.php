<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[ORM\Table(name: 'intervention')]
#[ORM\HasLifecycleCallbacks]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateIntervention = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTime $heureIntervention = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $technicien = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixRealise = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateRealisation = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTime $heureRealisation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $photos = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new DateTime();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (!$this->dateCreation) {
            $this->dateCreation = new DateTime();
        }
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

    public function getDateIntervention(): ?\DateTime
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(\DateTime $dateIntervention): static
    {
        $this->dateIntervention = $dateIntervention;
        return $this;
    }

    public function getHeureIntervention(): ?\DateTime
    {
        return $this->heureIntervention;
    }

    public function setHeureIntervention(\DateTime $heureIntervention): static
    {
        $this->heureIntervention = $heureIntervention;
        return $this;
    }

    public function getTechnicien(): ?string
    {
        return $this->technicien;
    }

    public function setTechnicien(?string $technicien): static
    {
        $this->technicien = $technicien;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getPrixRealise(): ?float
    {
        return $this->prixRealise;
    }

    public function setPrixRealise(?float $prixRealise): static
    {
        $this->prixRealise = $prixRealise;
        return $this;
    }

    public function getDateRealisation(): ?\DateTime
    {
        return $this->dateRealisation;
    }

    public function setDateRealisation(?\DateTime $dateRealisation): static
    {
        $this->dateRealisation = $dateRealisation;
        return $this;
    }

    public function getHeureRealisation(): ?\DateTime
    {
        return $this->heureRealisation;
    }

    public function setHeureRealisation(?\DateTime $heureRealisation): static
    {
        $this->heureRealisation = $heureRealisation;
        return $this;
    }

    public function getPhotos(): ?string
    {
        return $this->photos;
    }

    public function setPhotos(?string $photos): static
    {
        $this->photos = $photos;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
}
