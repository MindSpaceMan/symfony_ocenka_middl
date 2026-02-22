<?php

namespace App\Domain\Event;

final readonly class OrderCreated
{
    public function __construct(
        public int    $orderId,
        public int    $userId,
        public int    $serviceId,
        public string $email,
        public int    $priceSnapshot,
    )
    {
    }
}
