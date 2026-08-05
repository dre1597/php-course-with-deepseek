<?php

function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;                   // condição de parada
    }
    return $n * factorial($n - 1);    // chamada recursiva
}

echo factorial(5); // 120 (5 × 4 × 3 × 2 × 1)


function listCategories(array $categories, int $level = 0): void
{
    foreach ($categories as $cat) {
        echo str_repeat('  ', $level) . "- {$cat['name']}" . PHP_EOL;
        if (!empty($cat['children'])) {
            listCategories($cat['children'], $level + 1);
        }
    }
}

$tree = [
    [
        'name' => 'Electronics',
        'children' => [
            ['name' => 'Smartphones', 'children' => []],
            ['name' => 'Laptops', 'children' => []],
        ],
    ],
    [
        'name' => 'Books',
        'children' => [
            ['name' => 'Fiction', 'children' => []],
            ['name' => 'Technical', 'children' => []],
            ['name' => 'Biography', 'children' => []],
        ],
    ],
];

listCategories($tree);
/*
- Electronics
  - Smartphones
  - Laptops
- Books
  - Fiction
  - Technical
  - Biography
*/
