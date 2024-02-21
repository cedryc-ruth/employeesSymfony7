<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
    * @return Employee[] Returns an array of Project objects
    */
   public function findEmployeesNotYetInProject($projectId): array
   {
    //Les employés qui ne participent pas au projet passé en paramètre
    //SELECT * FROM `members` LEFT JOIN emp_project ON members.emp_no=emp_project.emp_no GROUP BY members.emp_no HAVING project_id!='1' OR project_id IS NULL;
    //--> un peu compliqué à traduire

    //--> plus facile avec une sous-requête
    //SELECT * FROM `members` WHERE members.emp_no
    //NOT IN ( SELECT DISTINCT members.emp_no FROM `members` INNER JOIN emp_project ON members.emp_no=emp_project.emp_no WHERE project_id='1' );
        return $this->createQueryBuilder('e')
                ->where('e.id NOT IN (SELECT DISTINCT em.id FROM App\Entity\Employee em INNER JOIN em.empProjects ep WHERE ep.project = :id)')
                ->setParameter('id', $projectId)
               ->orderBy('e.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
   }

//    /**
//     * @return Employee[] Returns an array of Employee objects
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

//    public function findOneBySomeField($value): ?Employee
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
