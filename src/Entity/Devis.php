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

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Client::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(length: 100)]
    private ?string $typeInstallation = null; // résidentiel, agricole, industriel...

    #[ORM\Column(nullable: true)]
    private ?int $nombrePanneaux = null;

    /** Surface des panneaux en m² (base du tarif). */
    #[ORM\Column(nullable: true)]
    private ?float $surface = null;

    /** plat | incline */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $toitType = null;

    /** facile | difficile */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $toitAcces = null;

    /** les_deux | eau | electricite | aucun */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $accesEauElec = null;

    /** Estimation HT calculée au moment de la demande (CHF). */
    #[ORM\Column(nullable: true)]
    private ?float $prixEstime = null;

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

    public function setNombrePanneaux(?int $nombrePanneaux): static
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public const TOIT_TYPES = ['plat' => 'Toit plat', 'incline' => 'Toit incliné'];
    public const TOIT_ACCES = ['facile' => 'Accès facile', 'difficile' => 'Accès difficile'];
    public const EAU_ELEC = [
        'les_deux' => 'Eau et électricité sur place',
        'eau' => 'Eau seulement',
        'electricite' => 'Électricité seulement',
        'aucun' => 'Ni eau ni électricité',
    ];

    /** Tarif HT par m² : 7.– jusqu'à 30 m², 6.– au-delà de 30 m², 5.– au-delà de 60 m². */
    public static function tarifM2(float $surface): float
    {
        if ($surface > 60) {
            return 5.0;
        }
        if ($surface > 30) {
            return 6.0;
        }
        return 7.0;
    }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $surface): static { $this->surface = $surface; return $this; }

    public function getToitType(): ?string { return $this->toitType; }
    public function setToitType(?string $toitType): static { $this->toitType = $toitType; return $this; }
    public function getToitTypeLabel(): ?string { return self::TOIT_TYPES[$this->toitType] ?? $this->toitType; }

    public function getToitAcces(): ?string { return $this->toitAcces; }
    public function setToitAcces(?string $toitAcces): static { $this->toitAcces = $toitAcces; return $this; }
    public function getToitAccesLabel(): ?string { return self::TOIT_ACCES[$this->toitAcces] ?? $this->toitAcces; }

    public function getAccesEauElec(): ?string { return $this->accesEauElec; }
    public function setAccesEauElec(?string $accesEauElec): static { $this->accesEauElec = $accesEauElec; return $this; }
    public function getAccesEauElecLabel(): ?string { return self::EAU_ELEC[$this->accesEauElec] ?? $this->accesEauElec; }

    public function getPrixEstime(): ?float { return $this->prixEstime; }
    public function setPrixEstime(?float $prixEstime): static { $this->prixEstime = $prixEstime; return $this; }
}
