<?php

namespace App\DataFixtures;

use App\Entity\Sujet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SujetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sujets = [
            ['name' => 'Mon animal', 'category' => 'category_0'],
        ];

        foreach ($sujets as $sujetData) {
            $sujet = new Sujet();
            $sujet->setName($sujetData['name']);


            $category = $this->getReference($sujetData['category'], \App\Entity\Category::class);
            $sujet->setCategory($category); 

            $manager->persist($sujet); 
        }

        $manager->flush(); 
    }


    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}