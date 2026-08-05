<?php

function sumAll(int ...$numbers): int
{
    return array_sum($numbers);
}

echo sumAll(1, 2, 3);       // 6
echo sumAll(10, 20, 30, 40); // 100
echo sumAll();               // 0


function logWithContext(string $level, string $message, mixed ...$context): void
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    if (!empty($context)) {
        echo "Context: " . json_encode($context, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

logWithContext('ERROR', 'Connection failure', host: 'db.local', port: 5432);
// [2026-08-04 10:30:00] [ERROR] Connection failure
// Context: {"host":"db.local","port":5432}



function createRow(string $col1, string $col2, string $col3): string
{
    return "{$col1} | {$col2} | {$col3}";
}

$data = ['PHP', '8.5', '2026'];
echo createRow(...$data); // PHP | 8.5 | 2026
