<?php

// === 09 — get_error_handler() and get_exception_handler() — PHP 8.5+ ===

// Register a custom handler
set_error_handler(function (int $severity, string $msg, string $file, int $line): bool {
    echo "[CUSTOM] {$msg} in {$file}:{$line}" . PHP_EOL;
    return true;
});

// PHP 8.5+: Get the current handler
$handler = get_error_handler();
var_dump($handler); // object(Closure)#1 (1) { ... }

// Restore the default handler then switch back to custom
$previous = get_error_handler();
restore_error_handler(); // back to PHP's default handler

trigger_error('Using default handler', E_USER_NOTICE);
// PHP Notice: Using default handler in ...

// Reapply the previous handler
set_error_handler($previous);
trigger_error('Using custom handler again', E_USER_NOTICE);
// [CUSTOM] Using custom handler again in ...

// Also works with exceptions
set_exception_handler(fn(Throwable $e) => error_log($e->getMessage()));
$excHandler = get_exception_handler();
var_dump($excHandler); // object(Closure)#2 (1) { ... }

// === Advanced Use Case — Error Handling Middleware ===

function withTemporaryErrorHandler(callable $fn, callable $tempHandler): mixed
{
    $previous = get_error_handler();   // PHP 8.5+
    set_error_handler($tempHandler);

    try {
        return $fn();
    } finally {
        // Restore the original handler
        if ($previous !== null) {
            set_error_handler($previous);
        } else {
            restore_error_handler();
        }
    }
}

// Usage: execute code with a temporary silent handler
withTemporaryErrorHandler(
    fn() => trigger_error('ignore this warning', E_USER_WARNING),
    fn() => true, // silent handler
);
// No output — the warning was suppressed
