<?php

namespace App\DataFixtures;

use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $items = [
            ['Оценка стоимости автомобиля', 700],
            ['Оценка стоимости дома', 3000],
            ['Оценка стоимости антиквариата', 1000],
            ['Оценка стоимости квартиры', 2000],
            ['Оценка стоимости велосипеда', 400],
        ];

        foreach ($items as [$name, $price]) {
            $service = (new Service())
                ->setName($name)
                ->setPrice($price);

            $manager->persist($service);
        }

        $manager->flush();
    }
}
