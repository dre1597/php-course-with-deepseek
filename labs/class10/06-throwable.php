<?php

// === 06 — Throwable Interface ===

try {
    throw new RuntimeException('Something went wrong', 500);
} catch (Throwable $e) {
    echo "Message: " . $e->getMessage() . PHP_EOL;
    echo "Code: "    . $e->getCode() . PHP_EOL;
    echo "File: "    . $e->getFile() . PHP_EOL;
    echo "Line: "    . $e->getLine() . PHP_EOL;

    // Compact stack trace
    $trace = $e->getTrace();
    foreach ($trace as $i => $frame) {
        $function = $frame['function'] ?? '???';
        $frameLine = $frame['line'] ?? '???';
        echo "  #{$i} {$function}() on line {$frameLine}" . PHP_EOL;
    }
}
