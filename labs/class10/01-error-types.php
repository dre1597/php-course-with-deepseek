<?php

// === 01 — Error Types and Exceptions vs Errors ===

// E_NOTICE — undefined variable
echo $undefinedVariable;
// Notice: Undefined variable: undefinedVariable

// E_WARNING — include nonexistent file
include 'nonexistent_file.php';
// Warning: include(nonexistent_file.php): Failed to open stream

// E_DEPRECATED — obsolete function
// (hypothetical example, old functions are marked as deprecated)
// Deprecated: Function nome_da_funcao() is deprecated

// E_ERROR — calling nonexistent function (in older versions, now it's Error/Throwable)
// nonexistentFunction(); — Fatal error

// Exceptions vs Errors — PHP 7.0+ converts most fatal errors to Throwable
try {
    // TypeError: function expects int, received string
    $result = array_sum('not an array');
} catch (TypeError $e) {
    echo "TypeError caught: " . $e->getMessage() . PHP_EOL;
}
