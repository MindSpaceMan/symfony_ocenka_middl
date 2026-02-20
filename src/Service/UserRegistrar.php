<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserRegistrar
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function register(User $user, string $plainPassword): User
    {
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

}
