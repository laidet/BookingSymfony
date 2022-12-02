<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBestUsers($limit = 4){

        return $this->createQueryBuilder('u')
                    ->select('u as user, AVG(c.rating) as avgRatings, COUNT(c) as sumComments')
                    ->join('u.ads','a')
                    ->join('a.comments','c')
                    ->groupBy('u')
                    ->having('sumComments > 3')
                    ->orderBy('avgRatings','DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult()
                    ;
    }
}
