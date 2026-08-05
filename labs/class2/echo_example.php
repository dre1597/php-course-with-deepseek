<?php

echo "Hello, world!\n";

echo "Hello", " ", "world!";  // Accepts multiple arguments (comma-separated)

echo "Hello" . " " . "world!"; // Or concatenation

// With expressions
$name = "Carlos";
echo "Welcome, " . $name . "!";
echo "Welcome, {$name}!";        // Interpolation (double quotes)
echo 'Welcome, ' . $name . '!';  // Single quotes + concatenation
