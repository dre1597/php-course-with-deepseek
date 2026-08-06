<?php

$data = ['a' => 1, 'b' => 2, 'c' => 3];

echo array_key_first($data); // a
echo array_key_last($data);  // c

// Equivalent without the function (less efficient):
// array_key_first: array_keys($data)[0] ?? null

$numbers = ['a' => 10, 'b' => 20, 'c' => 30, 'd' => 40];

$first = array_first($numbers);
$last   = array_last($numbers);

echo $first; // 10
echo $last;   // 40

// With empty array, returns null:
$empty = [];
var_dump(array_first($empty)); // NULL
var_dump(array_last($empty));  // NULL

$arr = ['x' => 100, 'y' => 200, 'z' => 300];

// PHP < 8.5: various verbose options
$first = reset($arr);                                    // modifies internal pointer
$first = $arr[array_key_first($arr)] ?? null;            // verbose
$first = array_values($arr)[0] ?? null;                  // inefficient

// PHP 8.5+: clean and direct
$first = array_first($arr);                              // 100, does not modify pointer
$last  = array_last($arr);                               // 300, does not modify pointer

$user = ['name' => 'Anna', 'email' => 'anna@email.com', 'age' => 28];

$keys = array_keys($user);
print_r($keys); // ['name', 'email', 'age']

$values = array_values($user);
print_r($values); // ['Anna', 'anna@email.com', 28]

// array_keys with value filter:
$numbers = [10, 20, 10, 30, 10, 40];
$indicesOf10 = array_keys($numbers, 10);
print_r($indicesOf10); // [0, 2, 4]
