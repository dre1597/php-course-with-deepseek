<?php

// var dump

$value = 42;

echo gettype($value);
echo get_debug_type($value);      // "int" (PHP 8.0+, more precise)

// Boolean checks
var_dump(is_int($value));         // bool(true)
var_dump(is_float($value));       // bool(false)
var_dump(is_string($value));      // bool(false)
var_dump(is_bool($value));        // bool(false)
var_dump(is_array($value));       // bool(false)
var_dump(is_object($value));      // bool(false)
var_dump(is_null($value));        // bool(false)
var_dump(is_numeric($value));
var_dump(is_scalar($value));
var_dump(is_callable($value));
var_dump(is_iterable($value));
var_dump(isset($value));
var_dump(empty($value));

// isset vs empty vs is_null
$var = null;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));

$var = 0;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));

$var = false;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));

// set type

$value = "123";
settype($value, "int");
echo $value;
var_dump($value);

$value = (int)"123";