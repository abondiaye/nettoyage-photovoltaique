<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByStatut(string $statut)
    {
        return $this->createQueryBuilder('r')
            ->where('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPending()
    {
        return $this->findByStatut('EN_ATTENTE');
    }

    public function findConfirmed()
    {
        return $this->findByStatut('CONFIRMEE');
    }

    public function findByDateRange(\DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateSouhaitee BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('r.dateSouhaitee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCustomer(int $customerId)
    {
        return $this->createQueryBuilder('r')
            ->where('r.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchCriteria(array $criteria)
    {
        $qb = $this->createQueryBuilder('r');

        if (isset($criteria['nom'])) {
            $qb->andWhere('r.customer.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        if (isset($criteria['email'])) {
            $qb->andWhere('r.customer.email LIKE :email')
                ->setParameter('email', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['telephone'])) {
            $qb->andWhere('r.customer.telephone LIKE :telephone')
                ->setParameter('telephone', '%' . $criteria['telephone'] . '%');
        }

        if (isset($criteria['numero'])) {
            $qb->andWhere('r.numero LIKE :numero')
                ->setParameter('numero', '%' . $criteria['numero'] . '%');
        }

        if (isset($criteria['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $criteria['statut']);
        }

        if (isset($criteria['ville'])) {
            $qb->andWhere('r.customer.ville = :ville')
                ->setParameter('ville', $criteria['ville']);
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getStats()
    {
        $qb = $this->createQueryBuilder('r');

        return [
            'total_pending' => count($this->findByStatut('EN_ATTENTE')),
            'total_confirmed' => count($this->findByStatut('CONFIRMEE')),
            'total_completed' => count($this->findByStatut('REALISEE')),
            'total_cancelled' => count($this->findByStatut('ANNULEE_CLIENT')) + count($this->findByStatut('ANNULEE_ADMIN')),
            'total_refused' => count($this->findByStatut('REFUSEE')),
        ];
    }
}
