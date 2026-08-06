<?php

$users = [
    ['name' => 'Anna',   'active' => true,  'points' => 120],
    ['name' => 'John',   'active' => false, 'points' => 200],
    ['name' => 'Mary',   'active' => true,  'points' => 85],
    ['name' => 'Peter',  'active' => true,  'points' => 150],
];

$firstInactive = array_find($users, fn(array $user): bool => !$user['active']);
print_r($firstInactive);
/*
Array
(
    [name] => John
    [active] => false
    [points] => 200
)
*/

// If no element is found, returns null
$notFound = array_find($users, fn(array $user): bool => $user['points'] > 999);
var_dump($notFound); // NULL

$stock = [
    'P001' => ['name' => 'Notebook',   'qty' => 0],
    'P002' => ['name' => 'Mouse',      'qty' => 45],
    'P003' => ['name' => 'Keyboard',   'qty' => 0],
    'P004' => ['name' => 'Monitor',    'qty' => 12],
];

$firstZero = array_find_key($stock, fn(array $product): bool => $product['qty'] === 0);
echo $firstZero; // P001

// Not found returns null
$key = array_find_key($stock, fn(array $product): bool => $product['qty'] > 100);
var_dump($key); // NULL

$values = [2, 4, 6, 7, 8, 10];

$hasOdd = array_any($values, fn(int $number): bool => $number % 2 !== 0);
var_dump($hasOdd); // bool(true)

$allNegative = array_any($values, fn(int $number): bool => $number < 0);
var_dump($allNegative); // bool(false)

// Practical: check if there are premium users
$users = [
    ['plan' => 'basic'],
    ['plan' => 'basic'],
    ['plan' => 'premium'],
];

$hasPremium = array_any($users, fn(array $user): bool => $user['plan'] === 'premium');
var_dump($hasPremium); // bool(true)

$ages = [18, 25, 30, 22, 19];

$allAdults = array_all($ages, fn(int $age): bool => $age >= 18);
var_dump($allAdults); // bool(true)

$allEven = array_all($ages, fn(int $age): bool => $age % 2 === 0);
var_dump($allEven); // bool(false)

// Practical: all passwords meet the minimum criteria
$passwords = ['ABcd1234!', 'XYzw5678@', 'PQrs9012#'];
$allSecure = array_all(
    $passwords,
    fn(string $string): bool => strlen($string) >= 8 && preg_match('/[A-Z]/', $string) && preg_match('/\d/', $string),
);
var_dump($allSecure); // bool(true)
