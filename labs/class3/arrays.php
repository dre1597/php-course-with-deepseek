<?php

// indexed arrays

$fruits = ['apple', 'banana', 'orange', 'grape'];
// Internamente é: [0 => 'apple', 1 => 'banana', 2 => 'orange', 3 => 'grape']

echo $fruits[0];        // apple
echo $fruits[2];        // orange

// Append: PHP acha a maior chave int + 1
$fruits[] = 'strawberry';  // Vira índice 4
echo $fruits[4];            // strawberry

// associative arrays

$user = [
    'name'      => 'Anna',
    'email'     => 'anna@example.com',
    'age'       => 28,
    'isAdmin'   => true,
];

echo $user['name'];   // Anna
echo $user['email'];  // anna@example.com

// multidimensional arrays

$products = [
    [
        'name' => 'Notebook',
        'price' => 3500.00,
        'tags' => ['electronics', 'computing'],
    ],
    [
        'name' => 'Mouse',
        'price' => 89.90,
        'tags' => ['peripherals'],
    ],
];

echo $products[0]['name'];
echo $products[1]['tags'][0];
