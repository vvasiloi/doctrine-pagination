<?php

namespace App\Repository;

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
     * @param bool   $withOrderBy
     *
     * @return QueryBuilder
     */
    public function createCategoryListQueryBuilder(string $categoryCode, bool $withOrderBy = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('productCategory')
            ->innerJoin('p.productCategories', 'productCategory')
            ->innerJoin('productCategory.category', 'category')
            ->where('category.code = :categoryCode')
            ->setParameter('categoryCode', $categoryCode)
        ;

        if ($withOrderBy) {
            $qb->orderBy('productCategory.position');
        }

        return $qb;
    }

    /**
     * @param string $categoryCode
     * @param bool   $withOrderBy
     *
     * @return QueryBuilder
     */
    public function createCategoryListQueryBuilderWithDoubleJoin(string $categoryCode, bool $withOrderBy = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('productCategory')
            ->innerJoin('p.productCategories', 'productCategory')
            ->innerJoin('p.productCategories', 'productCategoryFilter')
            ->innerJoin('productCategoryFilter.category', 'category')
            ->where('category.code = :categoryCode')
            ->orderBy('productCategoryFilter.position')
            ->setParameter('categoryCode', $categoryCode)
        ;

        if ($withOrderBy) {
            $qb->orderBy('productCategoryFilter.position');
        }

        return $qb;
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
