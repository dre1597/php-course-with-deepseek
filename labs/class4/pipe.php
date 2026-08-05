<?php
// PHP 8.5+

$words = [
    'apple',
    'banana',
    'apple',
    'orange',
];

// Without pipe: nested, hard to follow
$result = array_reverse(array_unique(array_map('strtoupper', $words)));

// With pipe: closures for multi-arg functions
$result = $words
        |> (fn($arr) => array_map(strtoupper(...), $arr))
        |> (fn($arr) => array_unique($arr))
        |> (fn($arr) => array_reverse($arr));

$data = "  Alice,28,New York\n Bob,35,Chicago\n  Charlie,22,Boston  ";

$users = $data
        |> trim(...)                                          // 1-arg → first-class callable
        |> (fn($str) => explode("\n", $str))                  // 2-arg → closure
        |> (fn($lines) => array_map(trim(...), $lines))
        |> (fn($lines) => array_filter($lines, strlen(...)))
        |> (fn($lines) => array_map(
            fn(string $line): array => (
                sscanf($line, '%[^,],%d,%s') |> (fn($parts) => ['name' => $parts[0], 'age' => $parts[1], 'city' => $parts[2]])
            ),
            $lines,
        ));

print_r($users);
/*
[
    ['name' => 'Alice',   'age' => 28, 'city' => 'New York'],
    ['name' => 'Bob',     'age' => 35, 'city' => 'Chicago'],
    ['name' => 'Charlie', 'age' => 22, 'city' => 'Boston'],
]
*/


function addTax(float $value, float $rate = 0.1): float
{
    return $value * (1 + $rate);
}

function applyDiscount(float $value, float $discount): float
{
    return $value * (1 - $discount);
}

function formatCurrency(float $value): string
{
    return '$' . number_format($value, 2);
}

$basePrice = 100.00;

$finalPrice = $basePrice
        |> (fn($v) => addTax($v, 0.15))
        |> (fn($v) => addTax($v, 0.05))
        |> (fn($v) => applyDiscount($v, 0.10))
        |> round(...)
        |> formatCurrency(...);

echo $finalPrice; // $108.68


// PHP 8.5+

function validateEmail(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException("Invalid email: {$email}");
    }
    return $email;
}

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$input = ' John@Example.COM ';

try {
    $cleanEmail = $input
            |> sanitize(...)
            |> normalizeEmail(...)
            |> validateEmail(...);

    echo "Processed email: {$cleanEmail}"; // Processed email: john@example.com
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
}
