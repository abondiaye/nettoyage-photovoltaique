<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $clientName = '';

    #[ORM\Column(length: 255)]
    private string $clientEmail = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $clientPhone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $requestedDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $confirmedDate = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending'; // pending, confirmed, refused, proposed

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adminNotes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->requestedDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): self
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): self
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getClientPhone(): ?string
    {
        return $this->clientPhone;
    }

    public function setClientPhone(?string $clientPhone): self
    {
        $this->clientPhone = $clientPhone;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getRequestedDate(): \DateTimeInterface
    {
        return $this->requestedDate;
    }

    public function setRequestedDate(\DateTimeInterface $requestedDate): self
    {
        $this->requestedDate = $requestedDate;
        return $this;
    }

    public function getConfirmedDate(): ?\DateTimeInterface
    {
        return $this->confirmedDate;
    }

    public function setConfirmedDate(?\DateTimeInterface $confirmedDate): self
    {
        $this->confirmedDate = $confirmedDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): self
    {
        $this->adminNotes = $adminNotes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
