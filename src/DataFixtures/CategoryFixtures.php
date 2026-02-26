<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Général',
            'Toilettage et soins',
            'Comportement et dressage',
            'Alimentation',
            'Santé et bien-être',
            'Activités et jeux',
            'Voyages et sorties',
            'Adoption et sauvetage',
            'Accessoires et équipements',
            'Autres animaux de compagnie'

        ];

        foreach ($categories as $key => $categoryName) {
            $category = new Category;
            $category->setName($categoryName);
            $manager->persist($category);

            $this->addReference('category_' . $key, $category);
        }

        $manager->flush();
    }
}