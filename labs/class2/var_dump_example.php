<?php

$name = "Anna";
var_dump($name);
// string(4) "Anna"

$age = 30;
var_dump($age);
// int(30)

$price = 19.99;
var_dump($price);
// float(19.99)

$fruits = ['apple', 'banana', 'orange'];
var_dump($fruits);
// array(3) {
//   [0]=> string(5) "apple"
//   [1]=> string(6) "banana"
//   [2]=> string(6) "orange"
// }

$active = true;
var_dump($active);
// bool(true)

$null = null;
var_dump($null);
// NULL

// var_dump accepts multiple arguments:
var_dump($name, $age, $price);
