<?php

namespace App\Service;

use App\Domain\Event\OrderCreated;
use App\Domain\ValueObject\Email;
use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use App\Service\Dto\OrderCreateResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class OrderCreator
{
    public function __construct(
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $dispatcher,
    )
    {
    }

    public function create(User $user, ?Service $service, string $emailRaw): OrderCreateResult
    {
        if ($service === null) {
            return OrderCreateResult::fail('Выберите услугу');
        }
        try {
            $email = Email::fromString($emailRaw);
        } catch (\InvalidArgumentException) {
            return OrderCreateResult::fail('Введите корректный email');
        }
        $order = (new Order())
            ->setUser($user)
            ->setService($service)
            ->setEmail((string)$email)
            ->setPriceSnapshot($service->getPrice());

        $this->em->persist($order);
        $this->em->flush();

        $this->dispatcher->dispatch(new OrderCreated(
            orderId: (int)$order->getId(),
            userId: (int)$user->getId(),
            serviceId: (int)$service->getId(),
            email: $email,
            priceSnapshot: $order->getPriceSnapshot() ?? 0,
        ));

        return OrderCreateResult::ok($order);
    }

}
