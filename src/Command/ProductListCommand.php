<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProductListCommand extends Command
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('product:list')
            ->addArgument('category', InputArgument::OPTIONAL, 'Category code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $category = $input->getArgument('category');

        if (null !== $category) {
            $products = $this->productRepository->createCategoryListQueryBuilder($category)
                ->getQuery()
                ->getResult();
        } else {
            $products = $this->productRepository->findAll();
        }

        $rows = array_map(function (Product $product) {
            $fetchedCategories = $this->getCategories($product);
            $this->productRepository->refresh($product);
            $actualCategories = $this->getCategories($product);

            return [
                'id' => $product->getId(),
                'code' => $product->getCode(),
                'fetched-categories' => implode(', ', $fetchedCategories),
                'actual-categories' => implode(', ', $actualCategories),
            ];
        }, $products);

        $io = new SymfonyStyle($input, $output);
        $io->table(array_keys($rows[0]), $rows);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getCategories(Product $product): array
    {
        return $product->getProductCategories()->map(function (ProductCategory $productCategory) {
            return $productCategory->getCategory()->getCode();
        })->toArray();
    }
}
