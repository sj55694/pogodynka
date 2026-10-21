<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }


    // ai generated method to find a location by city and country
    public function findByCityAndCountry(string $city, ?string $country = null): ?Location
    {
        $queryBuilder = $this->createQueryBuilder('location')
            ->andWhere('LOWER(location.name) = LOWER(:city)')
            ->setParameter('city', trim($city));

        if ($country !== null && trim($country) !== '') {
            $queryBuilder
                ->andWhere('UPPER(location.country) = :country')
                ->setParameter('country', strtoupper(trim($country)));
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

//    /**
//     * @return Location[] Returns an array of Location objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Location
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
