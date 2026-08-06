<?php

$name = 'PHP';

echo 'Hello, $name!';           // Hello, $name! (does NOT interpolate)
echo 'Cost: $ 99.90';           // Cost: $ 99.90
echo 'Escaped \'quote\'';       // Escaped 'quote'
echo 'Backslash \\ character';  // Backslash \ character

$language = 'PHP';
$version = 8.5;

echo "Learning $language $version";    // Learning PHP 8.5

// Escape sequences
echo "Line 1\nLine 2\nLine 3";
// Line 1
// Line 2
// Line 3

$fruits = ['apple', 'banana', 'orange'];
echo "First fruit: {$fruits[0]}";          // First fruit: apple

class User {
    public function getName(): string {
        return 'Mary';
    }
}
$user = new User();
echo "Name: {$user->getName()}";                 // Name: Mary

// Access associative array properties
$config = ['host' => 'localhost', 'port' => 3306];
echo "Connecting to {$config['host']}:{$config['port']}";
// Connecting to localhost:3306

$value = 42;
echo "Double of {$value} is " . ($value * 2); // Double of 42 is 84
