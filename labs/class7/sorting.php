<?php

$numbers = [30, 10, 50, 20, 40];

sort($numbers);
print_r($numbers); // [10, 20, 30, 40, 50]

rsort($numbers);
print_r($numbers); // [50, 40, 30, 20, 10]

$scores = [
    'John'  => 85,
    'Mary'  => 92,
    'Peter' => 78,
    'Anna'  => 95,
];

asort($scores);  // sort by value, preserve keys
print_r($scores);
/*
Array
(
    [Peter] => 78
    [John] => 85
    [Mary] => 92
    [Anna] => 95
)
*/

arsort($scores); // descending, preserve keys
print_r($scores);
/*
Array
(
    [Anna] => 95
    [Mary] => 92
    [John] => 85
    [Peter] => 78
)
*/

$date = [
    'zebra' => 1,
    'alpha' => 2,
    'gama'  => 3,
    'beta'  => 4,
];

ksort($date);
print_r($date);
/*
Array
(
    [alpha] => 2
    [beta] => 4
    [gama] => 3
    [zebra] => 1
)
*/

$products = [
    ['name' => 'Notebook',  'price' => 3500.00],
    ['name' => 'Mouse',     'price' => 89.90],
    ['name' => 'Keyboard',  'price' => 199.90],
    ['name' => 'Monitor',   'price' => 1200.00],
];

// Sort by ascending price
usort($products, fn(array $firstProduct, array $secondProduct): int => $firstProduct['price'] <=> $secondProduct['price']);

print_r($products);
/*
Array
(
    [0] => [Mouse, 89.9]
    [1] => [Keyboard, 199.9]
    [2] => [Monitor, 1200]
    [3] => [Notebook, 3500]
)
*/

$people = [
    ['name' => 'Anna',  'age' => 30],
    ['name' => 'John',  'age' => 25],
    ['name' => 'Mary',  'age' => 30],
    ['name' => 'Peter', 'age' => 25],
];

usort($people, function (array $first, array $second): int {
    // First by age, then by name
    return $first['age'] <=> $second['age']
        ?: $first['name'] <=> $second['name'];
});

print_r($people);
/*
[0] => [John, 25]
[1] => [Peter, 25]
[2] => [Anna, 30]
[3] => [Mary, 30]
*/
