<?php

$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn(int $n): int => $n * 2, $numbers);
print_r($doubled); // [2, 4, 6, 8, 10]

// With multiple arrays (PHP processes in parallel):
$array1 = [10, 20, 30];
$array2 = [1, 2, 3];
$sums = array_map(fn(int $first, int $second): int => $first + $second, $array1, $array2);
print_r($sums); // [11, 22, 33]

// With first-class callable (PHP 8.1+):
$names = [' ana ', ' Charles ', ' BEA '];
$clean = array_map(trim(...), $names);
print_r($clean); // ['ana', 'Charles', 'BEA']

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$evens = array_filter($numbers, fn(int $number): bool => $number % 2 === 0);
print_r($evens); // [1 => 2, 3 => 4, 5 => 6, 7 => 8, 9 => 10]

// Keep only non-empty strings:
$data = ['PHP', '', '8.5', false, null, '2026'];
$valid = array_filter($data);
print_r($data); // ['PHP', '8.5', '2026']

$numbers = [1, 2, 3, 4, 5];

$sum = array_reduce($numbers, fn(int $accumulator, int $current): int => $accumulator + $current, 0);
echo $sum; // 15

// Calculate the product of all elements:
$product = array_reduce($numbers, fn(int $acc, int $number): int => $acc * $number, 1);
echo $product; // 120 (5!)

// Build a complex structure (grouping):
$orders = [
    ['client' => 'Anna',  'total' => 150.00],
    ['client' => 'John',  'total' => 200.00],
    ['client' => 'Anna',  'total' => 75.00],
    ['client' => 'John',  'total' => 50.00],
];

$byClient = array_reduce(
    $orders,
    function (array $acc, array $order): array {
        $client = $order['client'];
        $acc[$client] = ($acc[$client] ?? 0) + $order['total'];
        return $acc;
    },
    [],
);

print_r($byClient);
/*
Array
(
    [Anna] => 225
    [John] => 250
)
*/

$values = [10.5, 20.3, 30.7, 40.1];

array_walk($values, function (float &$value, int $index): void {
    $value = round($value);
});

print_r($values); // [11, 20, 31, 40]

$prices = [100, 200, 300];
$tax = 0.1; // 10%

array_walk($prices, function (float &$price, int $index, float $rate): void {
    $price += $price * $rate;
}, $tax);

print_r($prices); // [110, 220, 330]
