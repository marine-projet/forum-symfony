<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * Repository pour l'entité Comment.
 *
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    public function findNewCommentsByUserAndFavorites(User $user)
    {
        $favoriteSubjects = $user->getFavoriteSubjects();

        if (empty($favoriteSubjects)) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->join('c.subject', 's')
            ->where('s IN (:favoriteSubjects)')
            ->andWhere('c.date > :lastVisit')
            ->setParameter('favoriteSubjects', $favoriteSubjects->toArray())
            ->setParameter('lastVisit', $user->getLastVisit() ?? $user->getCreatedAt())
            ->orderBy('c.date', 'DESC');

        return $qb->getQuery()->getResult();
    }
    public function findUnreadCommentsByUser($user)
{
    return $this->createQueryBuilder('c')
        ->where('c.isRead = false') // Filtrer ceux qui ne sont pas lus
        ->andWhere('c.user = :user') // Filtrer par l'utilisateur
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}
}
