<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('category', InputArgument::OPTIONAL, 'Category code')
            ->addOption('with-double-join', '', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $category = $input->getArgument('category');
        $withDoubleJoin = (bool)$input->getOption('with-double-join');

        if (null === $category) {
            $products = $this->productRepository->findAll();

            $this->displayProducts($products, $input, $output);

            return;
        }

        if ($withDoubleJoin) {
            $qb = $this->productRepository->createCategoryListQueryBuilderWithDoubleJoin($category);
        } else {
            $qb = $this->productRepository->createCategoryListQueryBuilder($category);
        }

        $products = $qb->getQuery()->getResult();

        $this->displayProducts($products, $input, $output);
    }

    /**
     * @param Product[]       $products
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function displayProducts(array $products, InputInterface $input, OutputInterface $output): void
    {
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
