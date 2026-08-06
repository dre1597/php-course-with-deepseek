<?php

$product = 'Notebook';
$price   = 3500.00;
$qty     = 2;

echo sprintf('Product: %s — Unit price: $ %.2f — Quantity: %d', $product, $price, $qty);
// Product: Notebook — Unit price: $ 3500.00 — Quantity: 2

$value = 1234.5678;

echo sprintf('$ %.2f', $value);        // $ 1234.57
echo sprintf('%\'.2f', $value);          // 1234.57 (single quote is the padding char)

// Zero-padded:
echo sprintf('%08d', 42);                // 00000042

// Fixed-width alignment:
echo sprintf("[%10s]", 'PHP');           // [       PHP]
echo sprintf("[%-10s]", 'PHP');          // [PHP       ]
echo sprintf("[%'.-10s]", 'PHP');        // [PHP.......]

printf('Date: %s, Time: %s', date('Y-m-d'), date('H:i'));
// Date: 2026-08-04, Time: 10:30

function formatLog(string $template, mixed ...$args): string
{
    $timestamp = date('Y-m-d H:i:s');
    $message = vsprintf($template, $args);
    return "[{$timestamp}] {$message}";
}

echo formatLog('User %s logged in from %s', 'admin', '192.168.1.1');
// [2026-08-04 10:30:00] User admin logged in from 192.168.1.1
