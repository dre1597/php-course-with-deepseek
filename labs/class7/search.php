<?php

$fruits = ['apple', 'banana', 'orange'];

var_dump(in_array('banana', $fruits));   // bool(true)
var_dump(in_array('grape', $fruits));    // bool(false)

// Strict type check (third parameter):
$numbers = [1, 2, '3'];
var_dump(in_array(3, $numbers));         // bool(true)  — coercion!
var_dump(in_array(3, $numbers, true));   // bool(false) — strict

$user = ['name' => 'Anna', 'email' => 'anna@email.com'];

var_dump(array_key_exists('name', $user));    // bool(true)
var_dump(array_key_exists('age', $user));   // bool(false)

// Different from isset():
$data = ['value' => null];
var_dump(array_key_exists('value', $data)); // bool(true)
var_dump(isset($data['value']));            // bool(false) — NULL is considered "not set"

$colors = [
    'primary'   => 'red',
    'secondary' => 'blue',
    'tertiary'  => 'green',
];

$key = array_search('blue', $colors);
echo $key; // secondary

// If not found:
$notFound = array_search('purple', $colors);
var_dump($notFound); // bool(false)

$values = [0 => 'first', 1 => 'second'];
$result = array_search('first', $values);

if ($result === false) {
    echo 'Not found';
} else {
    echo "Found at index: {$result}"; // Found at index: 0
}
