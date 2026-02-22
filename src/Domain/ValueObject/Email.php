<?php

namespace App\Domain\ValueObject;


final readonly class Email
{
    public string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $raw): self
    {
        $normalized = self::normalize($raw);

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email');
        }

        return new self($normalized);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public static function normalize(string $raw): string
    {
        $value = trim($raw);
        return mb_strtolower($value, 'UTF-8');
    }
}
