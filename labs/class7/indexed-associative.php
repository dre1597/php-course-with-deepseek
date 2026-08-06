<?php

$fruits = ['Apple', 'Banana', 'Orange', 'Grape'];

echo $fruits[0];   // Apple
echo $fruits[1];   // Banana
echo $fruits[2];   // Orange

$user = [
    'name'   => 'Mary Smith',
    'email'  => 'mary@email.com',
    'age'    => 28,
    'active' => true,
];

echo $user['name'];   // Mary Smith
echo $user['email'];  // mary@email.com

$data = [
    'id'      => 42,
    'name'    => 'Product X',
    0         => 'first',
    1         => 'second',
    'price'   => 99.90,
];

echo $data[0];        // first
echo $data['price'];  // 99.9
