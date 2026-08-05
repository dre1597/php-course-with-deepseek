<?php

$counter = 1;

while ($counter <= 5) {
    echo "Iteration {$counter}\n";
    $counter++;
}
// Iteration 1
// Iteration 2
// Iteration 3
// Iteration 4
// Iteration 5


// Reading a CSV file line by line with while
$handle = fopen('data.csv', 'r');
if ($handle === false) {
    die('Could not open the file');
}

$lineNumber = 0;
while (($line = fgetcsv($handle)) !== false) {
    $lineNumber++;
    echo "Line {$lineNumber}: " . implode(' | ', $line) . "\n";
}
fclose($handle);


$attempts = 0;
$maxAttempts = 3;

do {
    $attempts++;
    echo "Attempt {$attempts} out of {$maxAttempts}\n";

    // Simulates an operation that might fail
    $success = random_int(0, 1) === 1;

    if ($success) {
        echo "Operation completed successfully!\n";
        break;
    }

    echo "Failed. ";
} while ($attempts < $maxAttempts);

if ($attempts === $maxAttempts && !$success) {
    echo "All attempts failed.\n";
}
