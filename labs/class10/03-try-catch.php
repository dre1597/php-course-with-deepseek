<?php

// === 03 — try / catch / finally ===

function divide(float $dividend, float $divisor): float
{
    if ($divisor === 0.0) {
        throw new DivisionByZeroError('Division by zero is not allowed');
    }
    return $dividend / $divisor;
}

try {
    $result = divide(10, 0);
    echo "Result: {$result}";
} catch (DivisionByZeroError $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
} finally {
    echo "Finally block: always executed!" . PHP_EOL;
}

// Output:
// Error: Division by zero is not allowed
// File: /path/to/script.php
// Line: 6
// Finally block: always executed!

// === finally — always runs, even with return ===

function readFileContents(string $path): string
{
    $handle = null;
    try {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open: {$path}");
        }
        $content = fread($handle, filesize($path));
        return $content;
    } finally {
        // Ensures the file will always be closed
        if ($handle && is_resource($handle)) {
            fclose($handle);
            echo "[File closed]" . PHP_EOL;
        }
    }
}
