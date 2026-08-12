<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findBySearchTerm(string $term)
    {
        return $this->createQueryBuilder('c')
            ->where('c.nom LIKE :term OR c.prenom LIKE :term OR c.email LIKE :term OR c.telephone LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByEmail(string $email)
    {
        return $this->createQueryBuilder('c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByVille(string $ville)
    {
        return $this->createQueryBuilder('c')
            ->where('c.ville = :ville')
            ->setParameter('ville', $ville)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
