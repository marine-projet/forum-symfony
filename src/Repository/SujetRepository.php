<?php

namespace App\Repository;

use App\Entity\Sujet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Sujet.
 *
 * @extends ServiceEntityRepository<Sujet>
 *
 * @method Sujet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sujet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sujet[]    findAll()
 * @method Sujet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SujetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sujet::class);
    }

    public function findTopDiscussed(int $limit = 5): array
{
    return $this->createQueryBuilder('s')
        ->leftJoin('s.comments', 'c')        
        ->addSelect('COUNT(c.id) AS nbComments')
        ->groupBy('s.id')
        ->orderBy('nbComments', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult(); 
}
}
