<?php

namespace App\Repository;

use App\Entity\Commands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\DateTime;
/**
 * @extends ServiceEntityRepository<Commands>
 */
class CommandsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commands::class);
    }

//    /**
//     * @return Commands[] Returns an array of Commands objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commands
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function isCarRented(int $userId, int $carId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): bool
{
    $qb = $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->where('c.car_id = :carId')
        ->andWhere('(c.confirmed = 1 OR (c.confirmed IS NULL AND c.user_id = :userId))')
        ->andWhere('((c.start_date <= :startDate AND c.end_date >= :startDate) OR (c.start_date <= :endDate AND c.end_date >= :endDate))')
        ->setParameter('carId', $carId)
        ->setParameter('userId', $userId)
        ->setParameter('startDate', $startDate)
        ->setParameter('endDate', $endDate);

    return (int) $qb->getQuery()->getSingleScalarResult() > 0;
}


public function findActiveRentingCarsByUser(int $userId): array
{
    $qb = $this->createQueryBuilder('cmd')
        ->select('cmd','cmd.end_date','cmd.start_date', 'car.brand', 'car.model','DATE_DIFF(cmd.end_date, CURRENT_DATE()) AS remainingDays')
        ->innerJoin('cmd.car_id', 'car')
        ->where('cmd.user_id = :user_id')
        ->andWhere('cmd.start_date <= :today')
        ->andWhere('cmd.end_date >= :today AND cmd.confirmed=1')
        ->setParameter('user_id', $userId)
        ->setParameter('today', new \DateTime());

    return $qb->getQuery()->getResult();

 
    


}



}