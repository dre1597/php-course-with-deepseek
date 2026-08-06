<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/01-calculator.php';

class CalculatorTest extends TestCase
{
    public function testAddsTwoPositiveNumbers(): void
    {
        $this->assertEquals(5, add(2, 3));
    }

    public function testAddsNegativeNumbers(): void
    {
        $this->assertEquals(-5, add(-2, -3));
    }

    public function testAddsZero(): void
    {
        $this->assertEquals(7, add(7, 0));
    }
}
