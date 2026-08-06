<?php

// Values only
$colors = ['red', 'green', 'blue'];
foreach ($colors as $color) {
    echo $color . PHP_EOL;
}

// Key and value
$user = ['name' => 'Mary', 'email' => 'mary@email.com', 'age' => 28];
foreach ($user as $key => $value) {
    echo "{$key}: {$value}" . PHP_EOL;
}
/*
name: Mary
email: mary@email.com
age: 28
*/

$numbers = [1, 2, 3, 4, 5];

foreach ($numbers as &$value) {
    $value *= 2;
}
unset($value); // IMPORTANT: release the reference

print_r($numbers); // [2, 4, 6, 8, 10]

$items = [1, 2, 3];
foreach ($items as &$value) { $value *= 10; }
// unset($value); // forgotten!

foreach ($items as $value) {
    // $value is still a reference to $items[2]!
    // This corrupts the original array.
}
print_r($items); // unexpected result without unset

$empty = [];

if ($empty === []) {
    echo 'Array is empty!' . PHP_EOL;
}

foreach ($empty as $item) {
    echo $item;
}

$date = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);

foreach ($date as $key => $value) {
    echo "{$key} => {$value}" . PHP_EOL;
}
