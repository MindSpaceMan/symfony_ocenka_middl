<?php

namespace App\Service\Dto;

use App\Entity\Order;
use Psr\Log\LoggerInterface;

final readonly class OrderCreateResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public bool   $success,
        public ?Order $order,
        public array  $errors
    )
    {
    }

    public static function ok(Order $order): self
    {
        return new self(true, $order, []);
    }

    public static function fail(string ...$errors): self
    {
        return new self(false, null, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
