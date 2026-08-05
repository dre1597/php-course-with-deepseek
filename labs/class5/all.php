<?php

declare(strict_types=1);

function classifyNumbers(array $numbers): array
{
    $classification = [
        'even'      => [],
        'odd'       => [],
        'primes'    => [],
        'negatives' => [],
        'zeros'     => 0,
    ];

    foreach ($numbers as $number) {
        if ($number === 0) {
            $classification['zeros']++;
            continue;
        }

        if ($number < 0) {
            $classification['negatives'][] = $number;
            $number = abs($number);
        }

        if ($number % 2 === 0) {
            $classification['even'][] = $number;
        } else {
            $classification['odd'][] = $number;
        }

        if (isPrime($number)) {
            $classification['primes'][] = $number;
        }
    }

    return $classification;
}

function isPrime(int $n): bool
{
    if ($n <= 1) return false;
    if ($n <= 3) return true;

    for ($i = 2; $i <= sqrt($n); $i++) {
        if ($n % $i === 0) {
            return false;
        }
    }

    return true;
}

// Run it
$numbers = [-7, -3, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 17, 0, 42];
$result = classifyNumbers($numbers);

foreach ($result as $category => $values) {
    if ($category === 'zeros') {
        echo "Zeros found: {$values}\n";
    } else {
        echo ucfirst($category) . ": " . implode(', ', $values) . "\n";
    }
}
/*
Even: 2, 4, 6, 8, 10, 42
Odd: 7, 3, 3, 5, 7, 9, 11, 13, 17
Primes: 2, 7, 3, 3, 5, 7, 11, 13, 17
Negatives: -7, -3
Zeros found: 2
*/
