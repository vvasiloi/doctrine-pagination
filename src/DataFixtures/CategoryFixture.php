<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $category = new Category();
            $category->setCode('category-'.$i);
            $manager->persist($category);

            $this->addReference($category->getCode(), $category);
        }

        $manager->flush();
    }
}
