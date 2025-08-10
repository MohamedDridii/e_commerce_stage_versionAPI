<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }
    // In StockRepository.php
    public function findAllWithDetails()
    {
        return $this->createQueryBuilder('s')
            ->join('s.product', 'p')
            ->join('p.category', 'c')
            ->join('s.store', 'st')
            ->addSelect('p')
            ->addSelect('c')
            ->addSelect('st')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findAllWithDetailsForApi()
    {
        return $this->createQueryBuilder('s')
            ->join('s.product', 'p')
            ->join('p.category', 'c')
            ->join('s.store', 'st')
            ->addSelect('p')
            ->addSelect('c')
            ->addSelect('st')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();    
    }
    public function findStoresByProductIdDQL(int $productId){
        return $this->getEntityManager()
            ->createQuery('
                SELECT s.name, s.adress, st.quantity
                FROM App\Entity\Stock st 
                JOIN st.store s
                WHERE st.product = :productId
            ')
            ->setParameter('productId', $productId)
            ->getResult();
    }
    
    public function findRemainingStock(Product $product){
        return $this->createQueryBuilder('s')
                    ->select('COALESCE(SUM(s.quantity),0)')
                    ->where('s.product = :Product')
                    ->setParameter('Product', $product)
                    ->getQuery()
                    ->getSingleScalarResult();
    }
    //    /**
    //     * @return Stock[] Returns an array of Stock objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stock
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
