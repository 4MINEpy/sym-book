<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowDynamicProperties] class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;

    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //$manager->flush();


        $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['roles' => ['ROLE_ADMIN']]);

        if (!$existingAdmin) {

            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setFirstName('Admin');
            $admin->setLastName('User');
            $admin->setIsVerified(true);

            // Hash password before saving
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin003');
            $admin->setPassword($hashedPassword);

            $manager->persist($admin);
            $manager->flush();
        }
    }


}
