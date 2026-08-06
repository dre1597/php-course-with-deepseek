<?php

declare(strict_types=1);

function divide(float $a, float $b): float
{
    if ($b === 0.0) {
        throw new \DivisionByZeroError('Division by zero');
    }
    return $a / $b;
}

function withdraw(float $amount): float
{
    if ($amount < 0) {
        throw new \InvalidArgumentException('Amount cannot be negative', 400);
    }
    return $amount;
}
