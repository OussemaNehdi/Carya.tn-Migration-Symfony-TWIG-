<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $usersData = [
            ['John', 'Doe', 'john@example.com', 'password123', '2024-03-25', ['ROLE_USER'], 'California', 'United States', 'mezyen.jpg'],
            ['Alice', 'Smith', 'alice@example.com', 'securepass', '2024-03-25', ['ROLE_ADMIN'], 'New York', 'United States', 'mezyena.jpg'],
            ['Bob', 'Johnson', 'bob@example.com', 'bobspass', '2024-03-25', ['ROLE_USER'], 'Texas', 'United States', 'default.png'],
            ['Emily', 'Brown', 'emily@example.com', 'emilypass', '2024-03-26', ['ROLE_USER'], 'Florida', 'United States', 'default.png'],
            ['David', 'Wilson', 'david@example.com', 'davidpass', '2024-03-27', ['ROLE_BANNED'], 'Washington', 'United States', 'default.png'],
            ['Sophia', 'Taylor', 'sophia@example.com', 'sophiapass', '2024-03-27', ['ROLE_USER'], 'California', 'United States', 'default.png'],
        ];

        foreach ($usersData as $key => $data) {
            $user = new Users();
            $user->setFirstName($data[0]);
            $user->setLastName($data[1]);
            $user->setEmail($data[2]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data[3]));
            $user->setCreationDate(new \DateTime($data[4]));
            $user->setRoles($data[5]);
            $user->setState($data[6]);
            $user->setCountry($data[7]);
            $user->setProfileImage($data[8]);

            $manager->persist($user);
            $this->addReference('user_' . $key, $user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user']; // Define the group name for this fixture
    }
}