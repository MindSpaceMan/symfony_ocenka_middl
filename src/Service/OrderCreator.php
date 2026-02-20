<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class OrderCreator
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function create(User $user, Service $service, string $email): Order
    {
        $order = (new Order())
            ->setUser($user)
            ->setService($service)
            ->setEmail($email)
            ->setPriceSnapshot($service->getPrice());

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

}
