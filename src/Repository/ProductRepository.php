<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function countTotalStock(Product $product){
        return (int) $this->createQueryBuilder('p')
                ->select('Sum(s.quantity) as totalStock')
                ->join('p.stocks','s')
                ->where('p = :product')
                ->setParameter('product',$product)
                ->getQuery()
                ->getSingleScalarResult();
    }
    public function findGroupedProducts()
    {
        return $this->createQueryBuilder('p')
            ->select('p.name', 
                    'MIN(p.id) as id',
                    'SUM(s.quantity) as totalStock',
                    'COUNT(DISTINCT s.store) as storeCount')
            ->leftJoin('p.stocks', 's')
            ->groupBy('p.name')
            ->getQuery()
            ->getResult();
    }
    public function findWithStores($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.stocks', 's')
            ->leftJoin('s.store', 'st')
            ->addSelect('s')
            ->addSelect('st')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
