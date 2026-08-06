<?php

$admin = ['name' => 'Admin', 'role' => 'admin'];
$data = ['email' => 'admin@site.com', 'active' => true];

$user = [...$admin, ...$data];
print_r($user);
/*
Array
(
    [name] => Admin
    [role] => admin
    [email] => admin@site.com
    [active] => 1
)
*/

$classA = ['Anna', 'John', 'Mary'];
$classB = ['Peter', 'Bea'];
$classC = ['Charles'];

$all = [...$classA, ...$classB, ...$classC];
print_r($all); // ['Anna', 'John', 'Mary', 'Peter', 'Bea', 'Charles']

$original = ['first', 'fourth'];
$new = [$original[0], ...['second', 'third'], $original[1]];

print_r($new); // ['first', 'second', 'third', 'fourth']

$array1 = ['x' => 1, 'y' => 2];
$array2 = ['y' => 200, 'z' => 3];

$mergedResult = [...$array1, ...$array2];
print_r($mergedResult); // ['x' => 1, 'y' => 200, 'z' => 3]
