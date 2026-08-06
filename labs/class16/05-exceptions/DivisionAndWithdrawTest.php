<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/05-divide.php';

class DivisionAndWithdrawTest extends TestCase
{
    public function testDividesCorrectly(): void
    {
        $this->assertEquals(5.0, divide(10.0, 2.0));
    }

    public function testThrowsOnDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        $this->expectExceptionMessageIs('Division by zero');

        divide(10.0, 0.0);
    }

    public function testThrowsCustomException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);

        withdraw(-50.0);
    }
}
