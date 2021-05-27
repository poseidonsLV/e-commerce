<?php

namespace App\Repository;

use App\Entity\SoldProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @method SoldProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method SoldProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method SoldProducts[]    findAll()
 * @method SoldProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SoldProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoldProducts::class);
    }
    
    public function getMostSoldProduct(int $number): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT product_id,  count
            FROM sold_products
            GROUP BY product_id
            ORDER BY COUNT(*) DESC
            LIMIT :limit;
            ';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $number, PDO::PARAM_INT);
        $stmt->executeStatement();

        return $stmt->fetchAllAssociative();
    }

    // /**
    //  * @return SoldProducts[] Returns an array of SoldProducts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SoldProducts
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
