<?php

$defaults = [
    'host'     => 'localhost',
    'port'     => 3306,
    'charset'  => 'utf8mb4',
];

$userConfig = [
    'host'     => 'db.production.com',
    'username' => 'admin',
];

// Union: left-side keys take priority for duplicates
$config = $userConfig + $defaults;

print_r($config);
/*
[
    'host'     => 'db.production.com',  // from $userConfig (left side wins)
    'username' => 'admin',              // from $userConfig
    'port'     => 3306,                 // from $defaults (not in $userConfig)
    'charset'  => 'utf8mb4',            // from $defaults
]
*/


$a = [1, 2, 3];
$b = [4, 5, 6, 7];

print_r($a + $b);            // [1, 2, 3, 7] — indices 0,1,2 already in $a
print_r(array_merge($a, $b)); // [1, 2, 3, 4, 5, 6, 7] — concatenates everything


$a = ['apple', 'banana'];
$b = [0 => 'apple', 1 => 'banana'];
$c = ['banana', 'apple'];

var_dump($a == $b);   // true — same key/value pairs
var_dump($a === $b);  // true — same order and types
var_dump($a == $c);   // false — different values at positions
var_dump($a != $c);   // true
