<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

function sum(int $a, int $b): int
{
    return $a + $b;
}

class MathTest extends TestCase
{
    #[DataProvider('additionProvider')]
    public function testSumsCorrectly(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, sum($a, $b));
    }

    public static function additionProvider(): array
    {
        return [
            'positives'       => [2, 3, 5],
            'zeros'           => [0, 0, 0],
            'negative result' => [-2, 1, -1],
            'negative inputs' => [-5, -3, -8],
        ];
    }
}
