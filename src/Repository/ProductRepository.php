<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param string $categoryCode
     *
     * @return QueryBuilder
     */
    public function createCategoryListQueryBuilder(string $categoryCode): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('productCategory')
            ->addSelect('category')
            ->innerJoin('p.productCategories', 'productCategory')
            ->innerJoin('productCategory.category', 'category')
            ->where('category.code = :categoryCode')
            ->setParameter('categoryCode', $categoryCode)
        ;
    }

    /**
     * @param Product $product
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function refresh(Product $product): void
    {
        $this->_em->refresh($product);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
