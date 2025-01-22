<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mission>
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

       /**
        * @return Mission[] Returns an array of Mission objects
        */
       public function findAllMissionsByEmpNo($empNo): array
       {
            $conn = $this->getEntityManager()->getConnection();

            $sql = "SELECT * FROM `missions` 
                INNER JOIN emp_mission ON missions.id=emp_mission.mission_id 
                INNER JOIN members ON members.emp_no=emp_mission.emp_no 
                WHERE members.emp_no=:empNo
                AND STATUS <> 'done'";

            $resultSet = $conn->executeQuery($sql, ['empNo' => $empNo]);

            // returns an array of arrays (i.e. a raw data set)
            return $resultSet->fetchAllAssociative();
       }

    //    public function findOneBySomeField($value): ?Mission
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
