<?php

namespace App\DataFixtures;

use App\Entity\Cars;
use App\Entity\Commands;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class CommandsFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $commandsData = [
            ['car_id' => 1, 'user_id' => 2, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-01', 'end_date' => '2024-04-08', 'rental_period' => 7, 'confirmed' => false],
            ['car_id' => 6, 'user_id' => 1, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-05', 'end_date' => '2024-04-10', 'rental_period' => 5, 'confirmed' => true],
            ['car_id' => 3, 'user_id' => 3, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-02', 'end_date' => '2024-04-07', 'rental_period' => 5, 'confirmed' => true],
            ['car_id' => 4, 'user_id' => 4, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-03', 'end_date' => '2024-04-06', 'rental_period' => 3, 'confirmed' => true],
            ['car_id' => 5, 'user_id' => 5, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-04', 'end_date' => '2024-04-09', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 2, 'user_id' => 4, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-05', 'end_date' => '2024-04-10', 'rental_period' => 5, 'confirmed' => false],
            ['car_id' => 7, 'user_id' => 1, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-06', 'end_date' => '2024-04-11', 'rental_period' => 5, 'confirmed' => true],
            ['car_id' => 8, 'user_id' => 1, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-07', 'end_date' => '2024-04-12', 'rental_period' => 5, 'confirmed' => false],
            ['car_id' => 9, 'user_id' => 2, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-08', 'end_date' => '2024-04-13', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 10, 'user_id' => 2, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-09', 'end_date' => '2024-04-14', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 11, 'user_id' => 3, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-10', 'end_date' => '2024-04-15', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 12, 'user_id' => 3, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-11', 'end_date' => '2024-04-16', 'rental_period' => 5, 'confirmed' => true],
            ['car_id' => 13, 'user_id' => 3, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-12', 'end_date' => '2024-04-17', 'rental_period' => 5, 'confirmed' => false],
            ['car_id' => 14, 'user_id' => 5, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-13', 'end_date' => '2024-04-18', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 15, 'user_id' => 5, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-14', 'end_date' => '2024-04-19', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 16, 'user_id' => 5, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-15', 'end_date' => '2024-04-20', 'rental_period' => 5, 'confirmed' => null],
            ['car_id' => 17, 'user_id' => 2, 'rental_date' => '2024-03-25', 'start_date' => '2024-04-16', 'end_date' => '2024-04-21', 'rental_period' => 5, 'confirmed' => false],
        ];

        foreach ($commandsData as $data) {
            $command = new Commands();
            $command->setCarId($this->getReference('car_' . $data['car_id']));
            $command->setUserId($this->getReference('user_' . $data['user_id']));
            $command->setRentalDate(new \DateTime($data['rental_date']));
            $command->setStartDate(new \DateTime($data['start_date']));
            $command->setEndDate(new \DateTime($data['end_date']));
            $command->setRentalPeriod($data['rental_period']);
            $command->setConfirmed($data['confirmed']);

            $manager->persist($command);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class, // Specify UserFixtures as a dependency
            CarsFixtures::class, // Specify CarsFixtures as a dependency
        ];
    }

    public static function getGroups(): array
    {
        return ['commands']; // Define the group name for this fixture
    }
}

