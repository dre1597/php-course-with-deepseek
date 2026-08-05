<?php

function divide(float $a, float $b): float
{
    if ($b === 0.0) {
        throw new \DivisionByZeroError('Division by zero');
    }
    return $a / $b;
    // Code after return never runs
    echo "This line is never printed";
}

$result = divide(10, 2);
echo $result; // 5

// config.php
return [
    'host' => 'localhost',
    'database' => 'dev_db',
    'username' => 'root',
    'password' => 'password123',
];

// index.php
$config = require 'config.php';
echo $config['host']; // localhost