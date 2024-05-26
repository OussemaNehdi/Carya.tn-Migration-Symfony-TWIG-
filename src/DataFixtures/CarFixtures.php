<?php

namespace App\DataFixtures;

use App\Entity\Cars;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class CarsFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $carsData = [
            ['Toyota', 'Corolla', 'Red', 'corolla.jpg', 50000, 15000.00, 'user_0', true],
            ['Honda', 'Civic', 'Blue', 'civic.jpg', 60000, 17000.00, 'user_0', true],
            ['Ford', 'Fiesta', 'Silver', 'fiesta.jpg', 40000, 12000.00, 'user_2', true],
            ['Tesla', 'Model S', 'Black', 'model_s.jpg', 20000, 60000.00, 'user_3', false],
            ['Chevrolet', 'Camaro', 'Yellow', 'camaro.jpg', 30000, 35000.00, 'user_3', true],
            ['BMW', '3 Series', 'White', '3_series.jpg', 45000, 25000.00, 'user_1', false],
            ['Mercedes-Benz', 'E-Class', 'Gray', 'e_class.jpg', 55000, 30000.00, 'user_1', true],
            ['Fiat', 'Punto', 'Red', 'punto.jpg', 30000, 10000.00, 'user_2', true],
            ['Ferrari', 'F8', 'Red', 'F8.jpg', 10000, 200000.00, 'user_3', true],
            ['Audi', 'R8', 'Blue', 'R8.jpg', 15000, 150000.00, 'user_1', false],
            ['Lamborghini', 'Aventador', 'Yellow', 'aventador.jpg', 10000, 250000.00, 'user_0', true],
            ['Bugatti', 'Veyron', 'Black', 'veyron.jpg', 5000, 1000000.00, 'user_2', true],
            ['Porsche', '911', 'Silver', '911.jpg', 20000, 100000.00, 'user_0', false],
            ['McLaren', '720S', 'Orange', '720S.jpg', 10000, 200000.00, 'user_1', true],
            ['Koenigsegg', 'Agera', 'Blue', 'agera.jpg', 5000, 1000000.00, 'user_3', true],
            ['Pagani', 'Huayra', 'Silver', 'huayra.jpg', 5000, 1000000.00, 'user_2', true],
            ['Rolls-Royce', 'Phantom', 'Black', 'phantom.jpg', 20000, 500000.00, 'user_1', false],
            ['Bentley', 'Continental GT', 'White', 'continental_gt.jpg', 20000, 300000.00, 'user_0', true],
            ['Maserati', 'GranTurismo', 'Red', 'granturismo.jpg', 20000, 150000.00, 'user_3', true],
            ['Lotus', 'Evora', 'Orange', 'evora.jpg', 20000, 50000.00, 'user_2', true],
            ['Alfa Romeo', 'Giulia', 'Red', 'giulia.jpg', 20000, 50000.00, 'user_1', true],
            ['Jaguar', 'F-Type', 'Blue', 'f_type.jpg', 20000, 100000.00, 'user_0', false],
            ['Aston Martin', 'DB11', 'Silver', 'db11.jpg', 20000, 200000.00, 'user_3', true],
            ['Volvo', 'XC90', 'Black', 'xc90.jpg', 20000, 60000.00, 'user_2', true],
            ['Land Rover', 'Range Rover', 'White', 'range_rover.jpg', 20000, 80000.00, 'user_1', true],
            ['Jeep', 'Wrangler', 'Green', 'wrangler.jpg', 20000, 40000.00, 'user_0', false]
        ];

        foreach ($carsData as $key => $data) {
            $car = new Cars();
            $car->setBrand($data[0]);
            $car->setModel($data[1]);
            $car->setColor($data[2]);
            $car->setImage($data[3]);
            $car->setKm($data[4]);
            $car->setPrice($data[5]);
            // echo $this->getReference($data[6])->getID() . " ";  For debugging purposes
            $car->setOwnerId($this->getReference($data[6]));
            $car->setAvailable($data[7]);

            $manager->persist($car);

            $this->addReference('car_' . $key, $car);
        }

        $manager->flush();

    }

    public function getDependencies()
    {
        return [
            UserFixtures::class, // Specify UserFixtures as a dependency
        ];
    }

    public static function getGroups(): array
    {
        return ['cars']; // Define the group name for this fixture
    }
}