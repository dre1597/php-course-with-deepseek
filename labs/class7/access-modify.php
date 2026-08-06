<?php

$config = [
    'debug' => true,
    'host'  => 'localhost',
    'port'  => 3306,
];

echo $config['host'];                // localhost

// Access safely with null coalescing (PHP 7+)
echo $config['timeout'] ?? 30;       // 30 (key does not exist, use default)

// Chained null coalescing
echo $config['database']['mysql']['port'] ?? 5432; // 5432

$person = ['name' => 'John'];

$person['age']  = 25;         // add new key
$person['name'] = 'John S.';  // update existing value
$person[]       = 'extra';    // append with auto index (0)

print_r($person);
/*
Array
(
    [name] => John S.
    [age] => 25
    [0] => extra
)
*/

$colors = ['red', 'green', 'blue', 'yellow'];
unset($colors[1]);                           // remove 'green'

print_r($colors);
/*
Array
(
    [0] => red
    [2] => blue    <- indices are NOT reindexed!
    [3] => yellow
)
*/

$colors = array_values($colors);
print_r($colors);
/*
Array
(
    [0] => red
    [1] => blue
    [2] => yellow
)
*/
