<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisRepository::class)]
class Devis
{
    public const STATUT_NOUVEAU = 'nouveau';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_ACCEPTE = 'accepte';
    public const STATUT_REFUSE = 'refuse';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(length: 100)]
    private ?string $typeInstallation = null; // résidentiel, agricole, industriel...

    #[ORM\Column]
    private ?int $nombrePanneaux = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_NOUVEAU;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getTypeInstallation(): ?string
    {
        return $this->typeInstallation;
    }

    public function setTypeInstallation(string $typeInstallation): static
    {
        $this->typeInstallation = $typeInstallation;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }
}
