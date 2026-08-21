<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
#[ORM\HasLifecycleCallbacks]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numero = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(length: 255)]
    private ?string $typePrestation = null;

    #[ORM\Column]
    private ?int $nombrePanneaux = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateSouhaitee = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTime $heureSouhaitee = null;

    #[ORM\Column]
    private ?float $dureeEstimee = null;

    #[ORM\Column]
    private ?float $prixEstime = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaireClient = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'EN_ATTENTE';

    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'reservation')]
    private Collection $interventions;

    #[ORM\OneToMany(targetEntity: ReservationHistory::class, mappedBy: 'reservation', cascade: ['persist', 'remove'])]
    private Collection $history;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->history = new ArrayCollection();
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

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function getTypePrestation(): ?string
    {
        return $this->typePrestation;
    }

    public function setTypePrestation(string $typePrestation): static
    {
        $this->typePrestation = $typePrestation;
        return $this;
    }

    public function getNombrePanneaux(): ?int
    {
        return $this->nombrePanneaux;
    }

    public function setNombrePanneaux(int $nombrePanneaux): static
    {
        $this->nombrePanneaux = $nombrePanneaux;
        return $this;
    }

    public function getDateSouhaitee(): ?\DateTime
    {
        return $this->dateSouhaitee;
    }

    public function setDateSouhaitee(\DateTime $dateSouhaitee): static
    {
        $this->dateSouhaitee = $dateSouhaitee;
        return $this;
    }

    public function getHeureSouhaitee(): ?\DateTime
    {
        return $this->heureSouhaitee;
    }

    public function setHeureSouhaitee(\DateTime $heureSouhaitee): static
    {
        $this->heureSouhaitee = $heureSouhaitee;
        return $this;
    }

    public function getDureeEstimee(): ?float
    {
        return $this->dureeEstimee;
    }

    public function setDureeEstimee(float $dureeEstimee): static
    {
        $this->dureeEstimee = $dureeEstimee;
        return $this;
    }

    public function getPrixEstime(): ?float
    {
        return $this->prixEstime;
    }

    public function setPrixEstime(float $prixEstime): static
    {
        $this->prixEstime = $prixEstime;
        return $this;
    }

    public function getCommentaireClient(): ?string
    {
        return $this->commentaireClient;
    }

    public function setCommentaireClient(?string $commentaireClient): static
    {
        $this->commentaireClient = $commentaireClient;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setReservation($this);
        }
        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            if ($intervention->getReservation() === $this) {
                $intervention->setReservation(null);
            }
        }
        return $this;
    }

    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function addHistory(ReservationHistory $history): static
    {
        if (!$this->history->contains($history)) {
            $this->history->add($history);
            $history->setReservation($this);
        }
        return $this;
    }
}
