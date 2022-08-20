<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\Category;

class AppFixtures extends Fixture
{
    /**
     * Generate fake categories and products.
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category();
            $category->setName('Category '.$i);
            $manager->persist($category);
            for ($j = 1; $j < mt_rand(2, 10); $j++) {
                $price = number_format(mt_rand(10 * 2, 100 * 2) / 4, 3, '.', '');
                $product = new Product();
                $product->setName('Product '. $i . $j)
                        ->setPrice($price)
                        ->setQuantity(mt_rand(0, 10))
                        ->setCategory($category);
                $manager->persist($product);
            }
        }
        $manager->flush();
    }
}
