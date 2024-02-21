<?php

namespace App\Repository;

use App\Entity\EmpProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmpProject>
 *
 * @method EmpProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpProject[]    findAll()
 * @method EmpProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpProject::class);
    }

//    /**
//     * @return EmpProject[] Returns an array of EmpProject objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmpProject
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
