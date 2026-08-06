<?php
// Sanitization — cleans the value by removing unwanted characters

$email  = 'john@@example.com<script>';
$string = '<h1>Hello!</h1><script>alert("xss")</script>';
$url    = 'https://example.com/<script>';
$number = '+55 (11) 99999-8888';

// Remove invalid email characters
echo filter_var($email, FILTER_SANITIZE_EMAIL);
// john@@example.comscript — removed <> but kept Unicode characters

// Remove HTML tags
echo filter_var($string, FILTER_SANITIZE_STRING);
// Hello!alert("xss") — removed the tags

// Sanitize URL removing invalid characters
echo filter_var($url, FILTER_SANITIZE_URL);
// https://example.com/script

// Remove everything except digits, +, and -
echo filter_var($number, FILTER_SANITIZE_NUMBER_INT);
// +5511999998888

// Remove everything except digits and float characters (.,e,E,+,-)
echo filter_var('$ 1,299.90', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
// 1,299.90
