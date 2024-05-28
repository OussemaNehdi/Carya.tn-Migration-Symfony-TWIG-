<?php

namespace App\Repository;

use App\Entity\Commands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    $qb = $this->createQueryBuilder('cmd')
        ->select('COUNT(cmd.id)')
        ->where('cmd.car_id = :carId')
        ->andWhere('(cmd.confirmed = 1 OR (cmd.confirmed IS NULL AND cmd.user_id = :userId))')
        ->andWhere('((cmd.start_date <= :startDate AND cmd.end_date >= :startDate) OR (cmd.start_date <= :endDate AND cmd.end_date >= :endDate))')
        ->setParameter('carId', $carId)
        ->setParameter('userId', $userId)
        ->setParameter('startDate', $startDate)
        ->setParameter('endDate', $endDate);

    return (int) $qb->getQuery()->getSingleScalarResult() > 0;
}


}
