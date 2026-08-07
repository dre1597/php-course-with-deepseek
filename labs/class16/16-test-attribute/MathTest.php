<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Math.php';

class MathTest extends TestCase
{
    #[Test]
    public function multiplies_two_numbers(): void
    {
        $this->assertSame(15, multiply(3, 5));
    }

    #[Test]
    public function multiplies_with_zero(): void
    {
        $this->assertSame(0, multiply(7, 0));
    }

    #[Test]
    public function multiplies_negative_numbers(): void
    {
        $this->assertSame(-12, multiply(4, -3));
    }
}
