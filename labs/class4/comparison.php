<?php

$a = 5;
$b = "5";
$c = 10;

var_dump($a == $b);  // bool(true) — loose equality (type coercion)
var_dump($a == $c);  // bool(false)

var_dump($a === $b); // bool(false) — strict equality (same value AND type)
var_dump($a === 5);  // bool(true)

var_dump($a != $b);  // bool(false)
var_dump($a <> $b);  // bool(false) — same as !=, less common

var_dump($a !== $b); // bool(true) — types differ
var_dump($a !== 5);  // bool(false)

var_dump($a < $c);   // bool(true)
var_dump($a > $c);   // bool(false)
var_dump($a <= 5);   // bool(true)
var_dump($a >= 6);   // bool(false)

var_dump(0 == "0");          // true
var_dump(0 == "");           // true
var_dump(0 == "zero");       // false (PHP 8.0+: non-numeric string ≠ 0)
var_dump(0 == null);         // true
var_dump(0 == false);        // true
var_dump(0 == []);           // false (PHP 8.0+)
var_dump("0" == false);      // true
var_dump("0" == null);       // false
var_dump(null == false);     // true
var_dump("" == false);       // true
var_dump("" == null);        // true
var_dump([] == false);       // false (PHP 8.0+)
var_dump([] == null);        // true
var_dump([] == 0);           // false (PHP 8.0+)
var_dump(42 == true);        // true
var_dump(0 == false);        // true
var_dump(-1 == true);        // true


echo 1 <=> 1;   // 0
echo 1 <=> 2;   // -1
echo 2 <=> 1;   // 1

echo "a" <=> "b"; // -1 (string comparison, alphabetical order)
echo "b" <=> "a"; // 1
echo "a" <=> "a"; // 0

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numbers, fn($a, $b) => $a <=> $b);
print_r($numbers); // [1, 1, 2, 3, 4, 5, 6, 9]

$people = [
    ['name' => 'Alice', 'age' => 30],
    ['name' => 'Bob', 'age' => 25],
    ['name' => 'Charlie', 'age' => 30],
    ['name' => 'Diana', 'age' => 25],
];

usort($people, function (array $a, array $b): int {
    return $a['age'] <=> $b['age']
        ?: $a['name'] <=> $b['name'];
});

print_r($people);
/*
[
    ['name' => 'Bob',     'age' => 25],
    ['name' => 'Diana',   'age' => 25],
    ['name' => 'Alice',   'age' => 30],
    ['name' => 'Charlie', 'age' => 30],
]
*/
