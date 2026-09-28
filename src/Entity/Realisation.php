<?php

namespace App\Entity;

use App\Repository\RealisationRepository;
use Doctrine\ORM\Mapping as ORM;

/** A before/after cleaning project shown on the public blog page. */
#[ORM\Entity(repositoryClass: RealisationRepository::class)]
class Realisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(length: 255)]
    private string $location = '';

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    /** Public path of the "before" photo, e.g. /uploads/realisations/abc.jpg */
    #[ORM\Column(length: 255)]
    private string $imageBefore = '';

    #[ORM\Column(length: 255)]
    private string $imageAfter = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable('today');
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getLocation(): string { return $this->location; }
    public function setLocation(string $location): static { $this->location = $location; return $this; }

    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }

    public function getImageBefore(): string { return $this->imageBefore; }
    public function setImageBefore(string $path): static { $this->imageBefore = $path; return $this; }

    public function getImageAfter(): string { return $this->imageAfter; }
    public function setImageAfter(string $path): static { $this->imageAfter = $path; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
