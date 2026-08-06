<?php

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

echo $matrix[1][2]; // 6 (row 1, column 2)

// Iterating with nested foreach:
foreach ($matrix as $row => $values) {
    foreach ($values as $column => $value) {
        echo "[{$row}][{$column}] = {$value}" . PHP_EOL;
    }
}

$ecommerce = [
    'categories' => [
        [
            'id'     => 1,
            'name'   => 'Electronics',
            'products' => [
                ['id' => 101, 'name' => 'Smartphone',   'price' => 2500.00, 'stock' => 50],
                ['id' => 102, 'name' => 'Notebook',     'price' => 4500.00, 'stock' => 20],
            ],
        ],
        [
            'id'     => 2,
            'name'   => 'Books',
            'products' => [
                ['id' => 201, 'name' => 'Modern PHP',   'price' => 89.90,  'stock' => 100],
                ['id' => 202, 'name' => 'Clean Code',   'price' => 120.00, 'stock' => 75],
            ],
        ],
    ],
];

// Total stock value for Electronics
$totalElectronics = array_reduce(
    $ecommerce['categories'][0]['products'],
    fn(float $sum, array $product): float => $sum + ($product['price'] * $product['stock']),
    0.0,
);
echo "Total stock (Electronics): $ " . number_format($totalElectronics, 2, '.', ',');
// Total stock (Electronics): $ 215,000.00

$nested = [[1, 2], [3, 4], [5, 6]];

$flat = array_merge(...$nested);
print_r($flat); // [1, 2, 3, 4, 5, 6]

// Functional alternative with array_reduce:
$flat2 = array_reduce($nested, fn(array $acc, array $sub): array => [...$acc, ...$sub], []);
print_r($flat2); // [1, 2, 3, 4, 5, 6]
