<?php

namespace App\Repository;

use App\Entity\Cars;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Users;
use App\Entity\Commands;

/**
 * @extends ServiceEntityRepository<Cars>
 */
class CarsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cars::class);
    }

//    /**
//     * @return Cars[] Returns an array of Cars objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('cars.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('cars.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cars
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('cars.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function getAllCars(): array
{
    $qb = $this->createQueryBuilder('cars');
    
  
        $qb->andWhere('cars.available = :available')
           ->setParameter('available', 1);
    

    return $qb->orderBy('cars.id', 'ASC')
              ->getQuery()
              ->getResult();
}


public function findByFilters(array $filters): array
{
    $qb = $this->createQueryBuilder('cars');

    if (!empty($filters['brand'])) {
        $qb->andWhere('cars.brand IN (:brands)')
            ->setParameter('brands', $filters['brand']);
    }

    if (!empty($filters['model'])) {
        $qb->andWhere('cars.model IN (:models)')
            ->setParameter('models', $filters['model']);
    }

    if (!empty($filters['color'])) {
        $qb->andWhere('cars.color IN (:colors)')
            ->setParameter('colors', $filters['color']);
    }

    if (!empty($filters['km_min'])) {
        $qb->andWhere('cars.km >= :km_min')
            ->setParameter('km_min', $filters['km_min']);
    }

    if (!empty($filters['km_max'])) {
        $qb->andWhere('cars.km <= :km_max')
            ->setParameter('km_max', $filters['km_max']);
    }

    if (!empty($filters['price_min'])) {
        $qb->andWhere('cars.price >= :price_min')
            ->setParameter('price_min', $filters['price_min']);
    }

    if (!empty($filters['price_max'])) {
        $qb->andWhere('cars.price <= :price_max')
            ->setParameter('price_max', $filters['price_max']);
    }

    return $qb->getQuery()->getResult();
}

    public function constructFilterQuery(Request $request): array
    {
        $filters = $request->request->all(); // Get all POST parameters
        
        // Check if $filters is an array
        if (!is_array($filters)) {
            throw new \InvalidArgumentException("Invalid filters provided. Expected an array.");
        }
    
        $params = [];
    
        foreach ($filters as $key => $value) {
            if (!is_array($value)) {
                // If the value is not an array, split it into an array using comma as delimiter
                $params[$key] = explode(',', $value);
            } else {
                // If the value is already an array, keep it as is
                $params[$key] = $value;
            }
        }
    
        // You can return the $params array or perform additional operations
        
        return $params; // Return as JSON for demonstration
    }


    public function getDistinctValues($field)
    {
        $qb = $this->createQueryBuilder('cars');
        
        $qb->select("DISTINCT cars.$field AS $field")
           ->orderBy("cars.$field");
    
        $query = $qb->getQuery();
    
        $result = $query->getScalarResult();
    
        $distinctValues = array_column($result, $field);
    
        return $distinctValues;
    }

    public function getMaxValue($field)
    {
        return $this->createQueryBuilder('cars')
            ->select("MAX(cars.$field) AS max_value")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find active renting cars by user
     *
     * @param Users $user
     * @return Cars[]
     */
    public function findActiveRentingCarsByUser(Users $user): array
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('App\Entity\Commands', 'cmd', 'WITH', 'cmd.car_id = c.id')
            ->where('cmd.user_id = :user_id')
            ->andWhere('cmd.start_date <= :today')
            ->andWhere('cmd.end_date >= :today')
            ->setParameter('user_id', $user->getId())
            ->setParameter('today', new \DateTime())
            ->getQuery();

        return $qb->getResult();
    }

}


