<?php

namespace App\Repository;

use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
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

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countComments(Trick $trick): int
    {
        return intval($this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.trick = :trick')
            ->setParameter("trick", $trick)
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function loadComments(Trick $trick, int $start, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->where("c.trick = :trick")
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameter("trick", $trick)
            ->getQuery()
            ->getResult();
    }
}