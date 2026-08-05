<?php

function calculate(): void
{
    $localVar = 10;
    echo $localVar;
}

calculate();
// echo $localVar;         // Warning: Undefined variable $localVar


$counter = 0;

function increment(): void
{
    global $counter;
    $counter++;
}

increment();
increment();
echo $counter; // 2


$total = 100;

function applyDiscount(float $percentage): void
{
    $GLOBALS['total'] -= $GLOBALS['total'] * ($percentage / 100);
}

applyDiscount(10);
echo $total; // 90


$multiplier = 3;

$double = function (int $value) use ($multiplier): int {
    return $value * $multiplier;
};

echo $double(5); // 15


$accumulator = 0;

$add = function (int $value) use (&$accumulator): void {
    $accumulator += $value;
};

$add(10);
$add(5);
echo $accumulator; // 15