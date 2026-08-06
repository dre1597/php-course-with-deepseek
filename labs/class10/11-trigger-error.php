<?php

// === 11 — trigger_error User Errors ===

function calculateAge(int $birthYear): int
{
    $currentYear = (int) date('Y');

    if ($birthYear > $currentYear) {
        trigger_error(
            "Birth year ({$birthYear}) is greater than current year ({$currentYear})",
            E_USER_WARNING,
        );
        return 0;
    }

    if ($birthYear < 1900) {
        trigger_error(
            "Birth year too old: {$birthYear}",
            E_USER_NOTICE,
        );
    }

    return $currentYear - $birthYear;
}

$age = calculateAge(2050); // Warning: birth year greater than current
echo "Age: {$age}" . PHP_EOL; // 0

$age = calculateAge(1850); // Notice: birth year too old
echo "Age: {$age}" . PHP_EOL; // 176

// === Integrating with Custom Error Handler ===

function myErrorHandler(int $severity, string $msg, string $file, int $line): bool
{
    $levels = [
        E_USER_NOTICE     => 'NOTICE',
        E_USER_WARNING    => 'WARNING',
        E_USER_ERROR      => 'ERROR',
        E_USER_DEPRECATED => 'DEPRECATED',
    ];

    $level = $levels[$severity] ?? 'UNKNOWN';

    error_log("[{$level}] {$msg} in {$file}:{$line}");

    if ($severity === E_USER_ERROR) {
        echo json_encode(['fatal_error' => $msg]);
        exit(1);
    }

    return true; // do not run PHP's default handler
}

set_error_handler('myErrorHandler');

trigger_error('Obsolete configuration detected', E_USER_DEPRECATED);
trigger_error('Feature X will be removed in v3.0', E_USER_DEPRECATED);
