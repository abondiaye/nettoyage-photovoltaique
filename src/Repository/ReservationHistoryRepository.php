<?php

namespace App\Repository;

use App\Entity\ReservationHistory;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationHistory::class);
    }

    public function findByReservation(Reservation $reservation)
    {
        return $this->createQueryBuilder('h')
            ->where('h.reservation = :reservation')
            ->setParameter('reservation', $reservation)
            ->orderBy('h.dateAction', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAction(string $action)
    {
        return $this->createQueryBuilder('h')
            ->where('h.action = :action')
            ->setParameter('action', $action)
            ->orderBy('h.dateAction', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAdministrateur(string $administrateur)
    {
        return $this->createQueryBuilder('h')
            ->where('h.administrateur = :administrateur')
            ->setParameter('administrateur', $administrateur)
            ->orderBy('h.dateAction', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
