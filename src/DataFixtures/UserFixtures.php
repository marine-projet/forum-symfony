<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un compte administrateur
        $admin = new User();
        $admin->setUsername('Marine');
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'Aston1512_a'); // Définissez votre mot de passe
        $admin->setPassword($hashedPassword);
        $admin->setEmail('marine.contact.me15@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']); // Ajouter le rôle administrateur
        $admin->setIsVerified(true);

        $manager->persist($admin);

        // Charger un exemple d'utilisateur classique
        $user = new User();
        $user->setUsername('user');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'user123');
        $user->setPassword($hashedPassword);
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);

        $manager->persist($user);

        $manager->flush();
    }
}