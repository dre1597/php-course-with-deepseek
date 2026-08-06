<?php

$defaults = ['host' => 'localhost', 'port' => 3306, 'timeout' => 30];
$userConfig = ['host' => '192.168.1.10', 'user' => 'admin', 'timeout' => 60];

$final = array_merge($defaults, $userConfig);
print_r($final);
/*
Array
(
    [host] => 192.168.1.10    <- overwritten
    [port] => 3306
    [timeout] => 60           <- overwritten
    [user] => admin
)
*/

// Merge indexed arrays
$array1 = [10, 20, 30];
$array2 = [40, 50];
$merged = array_merge($array1, $array2);

print_r($merged); // [10, 20, 30, 40, 50]

$keys = ['name', 'email', 'age'];
$values = ['Charles', 'charles@email.com', 32];

$user = array_combine($keys, $values);
print_r($user);
/*
Array
(
    [name] => Charles
    [email] => charles@email.com
    [age] => 32
)
*/

$numbers = [10, 20, 30, 40, 50, 60];

$fatia = array_slice($numbers, 2, 3);     // from index 2, take 3 elements
print_r($fatia);                           // [30, 40, 50]

print_r(array_slice($numbers, -2));        // last 2: [50, 60]
print_r(array_slice($numbers, 0, 4));      // first 4: [10, 20, 30, 40]

$date = [5 => 'a', 10 => 'b', 15 => 'c'];
print_r(array_slice($date, 1, 2, true));
/*
Array
(
    [10] => b
    [15] => c
)
*/

$colors = ['red', 'green', 'blue', 'yellow', 'purple'];

// Remove 2 elements starting at index 2
$removed = array_splice($colors, 2, 2);
print_r($removed); // ['blue', 'yellow']
print_r($colors);     // ['red', 'green', 'purple']

$fruits = ['apple', 'banana', 'orange'];
array_splice($fruits, 1, 0, ['grape', 'pear']);  // insert at position 1, remove 0

print_r($fruits); // ['apple', 'grape', 'pear', 'banana', 'orange']
