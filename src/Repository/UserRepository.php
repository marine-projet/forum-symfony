<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Comment;

/**
 * Repository pour l'entité User.
 *
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    public function findNewCommentsForUser($user, \DateTime $since): array
{
    return $this->getEntityManager()->getRepository(Comment::class)
        ->createQueryBuilder('c')
        ->join('c.subject', 's')
        ->where(':user MEMBER OF s.favoritedBy')
        ->andWhere('c.createdAt > :since')
        ->setParameter('user', $user)
        ->setParameter('since', $since)
        ->getQuery()
        ->getResult();
}
}

