<?php
// Common validations
$email   = 'test@example.com';
$url     = 'https://www.php.net';
$ip      = '192.168.0.1';
$integer = '42';
$boolean = 'yes';

var_dump(filter_var($email, FILTER_VALIDATE_EMAIL));      // 'test@example.com' (valid)
var_dump(filter_var('invalid-email', FILTER_VALIDATE_EMAIL)); // false
var_dump(filter_var($url, FILTER_VALIDATE_URL));          // 'https://www.php.net'
var_dump(filter_var($ip, FILTER_VALIDATE_IP));            // '192.168.0.1'
var_dump(filter_var($integer, FILTER_VALIDATE_INT));      // 42
var_dump(filter_var('42.5', FILTER_VALIDATE_INT));        // false

// Validate number with range
$age = 25;
var_dump(filter_var($age, FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 0,
        'max_range' => 150,
    ]
])); // 25

// Validate with flags
var_dump(filter_var($boolean, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE));
// true ('yes' counts as true)

// Values considered true: '1', 'true', 'on', 'yes'
// Values considered false: '0', 'false', 'off', 'no'
