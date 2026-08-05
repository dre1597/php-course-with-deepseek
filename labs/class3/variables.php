<?php

$name = "Mary";
$age = 30;
$height = 1.72;
$active = true;

$name = "John";
$alternateName = "Mary";
$alternateFullName = "Charles";

echo $name;
echo $alternateName;
echo $alternateFullName;
// Three different variables!

// ✅ Valid
$_variable = 1;
$userName = "Anna";
$total2 = 100;
$café = "hot";              // Unicode — works, but avoid
$válida = "valid";
$specialChar = "special";   // Works, but terrible practice

// ❌ Invalid
// $1place = "error";       // Cannot start with a number
// $my-name = "error";      // Hyphen not allowed
// $my name = "error";      // Space not allowed
