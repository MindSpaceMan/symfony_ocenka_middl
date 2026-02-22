<?php

namespace App\EventListener;

use App\Domain\Event\OrderCreated;
use Psr\Log\LoggerInterface;

final readonly class OrderCreatedListener
{

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(OrderCreated $event): void
    {
        $this->logger->info('Order created', [
            'orderId' => $event->orderId,
            'userId' => $event->userId,
            'serviceId' => $event->serviceId,
            'email' => $event->email,
            'price' => $event->priceSnapshot,
        ]);
        // отправить почту
        // или поставить любую тяжелую задачу в фон.
    }

}
