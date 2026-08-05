<?php

$greeting = function (string $name): string {
    return "Hello, {$name}!";
};

echo $greeting('Anna'); // Hello, Anna!


$names = ['John', 'Mary', 'Peter'];

$mapped = array_map(function (string $name): string {
    return strtoupper($name);
}, $names);

print_r($mapped); // ['JOHN', 'MARY', 'PETER']


$multiplier = 2;
$values = [1, 2, 3, 4, 5];

$result = array_map(fn(int $number): int => $number * $multiplier, $values);

print_r($result); // [2, 4, 6, 8, 10]


$format = fn(int|float $value): string => number_format($value, 2, '.', ',');

echo $format(1234.5); // 1,234.50
