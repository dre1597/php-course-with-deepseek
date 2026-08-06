<?php

// === 08 — Global Exception Handler ===

function exceptionHandler(Throwable $e): void
{
    $timestamp = date('Y-m-d H:i:s');
    $class = get_class($e);
    $message = $e->getMessage();
    $file  = $e->getFile();
    $line    = $e->getLine();

    $log = <<<LOG
[{$timestamp}] {$class}: {$message}
  File: {$file}:{$line}
  Trace:
{$e->getTraceAsString()}
----------------------------------------
LOG;

    error_log($log, 3, __DIR__ . '/logs/exceptions.log');

    // Return a user-friendly response
    http_response_code(500);
    echo json_encode([
        'error'  => 'Internal server error',
        'code' => $e->getCode(),
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

set_exception_handler('exceptionHandler');

// From here on, uncaught exceptions call exceptionHandler()
// throw new RuntimeException('Test global failure');

// === Combining with Error Handler ===

function errorHandler(
    int $severity,
    string $message,
    string $file,
    int $line,
): bool {
    if (!(error_reporting() & $severity)) {
        // This error level is not configured for reporting
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler('errorHandler');

// Now notices and warnings become catchable exceptions:
try {
    echo $undefinedVariable;
} catch (ErrorException $e) {
    echo "Converted error: " . $e->getMessage() . PHP_EOL;
}
