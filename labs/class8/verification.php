<?php

$url = 'https://api.example.com/v1/users';

var_dump(str_contains($url, 'https'));         // bool(true)
var_dump(str_contains($url, 'ftp'));           // bool(false)

// Much more readable than strpos:
// Before:  strpos($url, 'https') !== false
// Now:  str_contains($url, 'https')

$email = 'user@domain.com';

var_dump(str_starts_with($email, 'admin'));    // bool(false)
var_dump(str_starts_with($email, 'user'));   // bool(true)

// Simple URL validation:
$url = 'https://example.com';
if (str_starts_with($url, 'https://')) {
    echo 'Secure connection';
}

$file = 'financial_report.pdf';

var_dump(str_ends_with($file, '.pdf'));     // bool(true)
var_dump(str_ends_with($file, '.docx'));    // bool(false)

// Filter allowed extensions:
$allowed = ['.jpg', '.png', '.gif', '.webp'];
$upload = 'profile_photo.png';

$valid = array_any($allowed, fn(string $ext): bool => str_ends_with($upload, $ext));
var_dump($valid); // bool(true)

function validateEmail(string $email): bool
{
    return str_contains($email, '@')
        && !str_starts_with($email, '@')
        && !str_ends_with($email, '@')
        && str_contains($email, '.');
}

var_dump(validateEmail('user@domain.com'));  // bool(true)
var_dump(validateEmail('@domain.com'));      // bool(false)
var_dump(validateEmail('user@'));             // bool(false)
