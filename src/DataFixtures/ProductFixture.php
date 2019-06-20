<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = range(1, 5);

        for ($i = 1; $i <= 20; $i++) {
            $product = new Product();
            $product->setCode('product-'.$i);
            $manager->persist($product);

            foreach ((array)array_rand($categories, 3) as $index) {
                $category = $this->getReference('category-'.$categories[$index]);
                $productCategory = (new ProductCategory())->setCategory($category);
                $manager->persist($productCategory);
                $product->addProductCategory($productCategory);
            }

            $this->addReference($product->getCode(), $product);
        }

        $manager->flush();
    }
}
