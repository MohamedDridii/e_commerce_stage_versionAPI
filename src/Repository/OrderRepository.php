<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Client;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
   
    public function countOrdersToday()
    {
        $today=new DateTimeImmutable('today');
        $tommorow=(new DateTimeImmutable('today'))->modify('+1 day');
        $qb=$this->createQueryBuilder('o')
                ->select('COUNT(o.id)')
                ->where('o.createdAt BETWEEN :today AND :tommorow')
                ->setParameter('today',$today)
                ->setParameter('tommorow',$tommorow);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function CountProfitToday()
    {
        $today=new DateTimeImmutable('today');
        $tommorow=(new DateTimeImmutable('today'))->modify('+1 day');
        $qb=$this->createQueryBuilder('o')
                ->select('SUM(ol.quantity * p.price)')
                ->leftJoin('o.orderLines','ol')
                ->leftJoin('ol.product','p')
                ->where('o.createdAt BETWEEN :today AND :tommorow')
                ->setParameter('today',$today)
                ->setParameter('tommorow',$tommorow);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
