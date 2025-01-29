<?php

namespace App\Repository;

use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Training>
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

       /**
        * @return Training[] Returns an array of Training objects
        */
       public function findByEmpNo($empNo): array
       {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "
                SELECT `trainings`.* FROM `trainings` INNER JOIN emp_training ON trainings.code=emp_training.training_code 
                WHERE emp_training.emp_no=:empNo;
                ";

            $resultSet = $conn->executeQuery($sql, ['empNo' => $empNo]);

            // returns an array of arrays (i.e. a raw data set)
            return $resultSet->fetchAllAssociative();
       }

       /**
        * @return Training[] Returns an array of Training objects
        */
        public function findAvailableTrainings($empNo): array
        {
             $conn = $this->getEntityManager()->getConnection();
 
             $sql = "
             SELECT `trainings`.* FROM trainings LEFT JOIN emp_training ON trainings.code=emp_training.training_code WHERE code NOT IN(SELECT code FROM `trainings` LEFT JOIN emp_training ON trainings.code=emp_training.training_code
             WHERE (emp_training.emp_no=:empNo))
              AND (places>(SELECT COUNT(*) FROM trainings 
                 WHERE code=emp_training.training_code) OR places='-1');
                ";
 
             $resultSet = $conn->executeQuery($sql, ['empNo' => $empNo]);
 
             // returns an array of arrays (i.e. a raw data set)
             return $resultSet->fetchAllAssociative();
        }

    //    public function findOneBySomeField($value): ?Training
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
