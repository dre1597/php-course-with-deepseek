<?php

$email = 'user@domain.com.br';

// Check email format (simplified)
$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (preg_match($pattern, $email)) {
    echo 'Valid email';
} else {
    echo 'Invalid email';
}

$date = 'Date: 2026-08-04';

if (preg_match('/Date: (\d{4})-(\d{2})-(\d{2})/', $date, $matches)) {
    print_r($matches);
    /*
    Array
    (
        [0] => Date: 2026-08-04
        [1] => 2026  // year
        [2] => 08    // month
        [3] => 04    // day
    )
    */
    echo "Year: {$matches[1]}, Month: {$matches[2]}, Day: {$matches[3]}";
    // Year: 2026, Month: 08, Day: 04
}

$html = '<a href="/home">Home</a> <a href="/about">About</a> <a href="/contact">Contact</a>';

preg_match_all('/href="([^"]+)"/', $html, $matches);
print_r($matches[1]); // ['/home', '/about', '/contact']

// Remove everything that is not a digit (e.g., format phone)
$phone = '(555) 98765-4321';
$onlyNumbers = preg_replace('/\D/', '', $phone);
echo $onlyNumbers; // 555987654321

// Mask part of an email
$email = 'secret.user@provider.com.br';
$masked = preg_replace('/(.{3}).*(@.*)/', '$1***$2', $email);
echo $masked; // sec***@provider.com.br

// Replace multiple spaces with a single one
$text = 'PHP     is         awesome';
$clean = preg_replace('/\s+/', ' ', $text);
echo $clean; // PHP is awesome

$csv = 'John, Mary; Peter| Anna, Bea';
$names = preg_split('/[,;|]\s*/', $csv);
print_r($names); // ['John', 'Mary', 'Peter', 'Anna', 'Bea']
