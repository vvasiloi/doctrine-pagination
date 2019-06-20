<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
            ->addOption('with-double-join', '', InputOption::VALUE_NONE)
            ->addOption('fetch-join-collection', '', InputOption::VALUE_NONE)
            ->addOption('with-output-walkers', '', InputOption::VALUE_NONE)
            ->addOption('without-output-walkers', '', InputOption::VALUE_NONE)
            ->addOption('with-order-by', '', InputOption::VALUE_NONE)
            ->addOption('limit', '', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $category = $input->getArgument('category');
        $withDoubleJoin = $input->getOption('with-double-join');
        $withOutputWalkers = $input->getOption('with-output-walkers');
        $withoutOutputWalkers = $input->getOption('without-output-walkers');
        $useOutputWalkers = ($withOutputWalkers xor $withoutOutputWalkers) ? $withOutputWalkers : null;
        $fetchJoinCollection = $input->getOption('fetch-join-collection');
        $withOrderBy = $input->getOption('with-order-by');
        $limit = (int)$input->getOption('limit');

        if (null === $category) {
            $products = $this->productRepository->findAll();

            $this->displayProducts($products, $input, $output);

            return;
        }

        if ($withDoubleJoin) {
            $qb = $this->productRepository->createCategoryListQueryBuilderWithDoubleJoin($category, $withOrderBy);
        } else {
            $qb = $this->productRepository->createCategoryListQueryBuilder($category, $withOrderBy);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        $paginator = new Paginator($qb, $fetchJoinCollection);
        $paginator->setUseOutputWalkers($useOutputWalkers);
        $products = iterator_to_array($paginator->getIterator());

        $this->displayProducts($products, $input, $output);
    }

    /**
     * @param Product[]       $products
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function displayProducts(array $products, InputInterface $input, OutputInterface $output): void
    {
        $headers = ['id', 'code', 'fetched-categories (position)', 'actual-categories (position)'];
        $rows = array_map(function (Product $product) {
            $fetchedCategories = $this->getCategories($product);
            $this->productRepository->refresh($product);
            $actualCategories = $this->getCategories($product);

            return [
                $product->getId(),
                $product->getCode(),
                implode(', ', $fetchedCategories),
                implode(', ', $actualCategories),
            ];
        }, $products);

        $io = new SymfonyStyle($input, $output);
        $io->table($headers, $rows);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getCategories(Product $product): array
    {
        return $product->getProductCategories()->map(function (ProductCategory $productCategory) {
            return sprintf('%s (%d)', $productCategory->getCategory()->getCode(), $productCategory->getPosition());
        })->toArray();
    }
}
