<?php

$age = 20;
$status = $age >= 18 ? "Adult" : "Minor";
echo $status; // Adult

$grade = 7.5;

// Works, but unreadable
$concept = $grade >= 9 ? 'A' : ($grade >= 7 ? 'B' : ($grade >= 5 ? 'C' : 'D'));

// Better: use match (PHP 8.0+)
$concept = match (true) {
    $grade >= 9 => 'A',
    $grade >= 7 => 'B',
    $grade >= 5 => 'C',
    default => 'D',
};

echo $concept; // B

// If the first operand is truthy, use it; otherwise, use the second
$name = $_GET['name'] ?: 'Guest';
// Roughly equivalent to:
$name = $_GET['name'] ? $_GET['name'] : 'Guest';

$counter = 0;
$result = $counter ?: 10;
echo $result; // 10 — 0 is falsy
