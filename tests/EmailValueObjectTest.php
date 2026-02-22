<?php

namespace App\Tests;

use App\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailValueObjectTest extends TestCase
{
    public function testNormalize(): void
    {
        $email = Email::fromString('  TeSt@Mail.Com  ');
        $this->assertSame('test@mail.com', (string)$email);
    }

    public function testInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('not-an-email');
    }
}
