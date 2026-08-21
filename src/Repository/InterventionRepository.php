<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function findByDateRange(\DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('i')
            ->where('i.dateIntervention BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('i.dateIntervention', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming()
    {
        return $this->createQueryBuilder('i')
            ->where('i.dateIntervention > :now AND i.dateRealisation IS NULL')
            ->setParameter('now', new \DateTime())
            ->orderBy('i.dateIntervention', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCompleted()
    {
        return $this->createQueryBuilder('i')
            ->where('i.dateRealisation IS NOT NULL')
            ->orderBy('i.dateRealisation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
