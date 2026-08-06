<?php

$hex = 'FF00AA';
print_r(str_split($hex, 2)); // ['FF', '00', 'AA']

// Without length, splits into characters:
print_r(str_split('PHP')); // ['P', 'H', 'P']

// str_split fails with multibyte characters:
$str_split_result = str_split('café');
print_r($str_split_result); // ['c', 'a', 'f', '�', '�'] — corrupted!

// mb_str_split works for basic multibyte:
print_r(mb_str_split('café')); // ['c', 'a', 'f', 'é'] — OK

// grapheme_str_split handles everything, including compound emojis:
$flag = '🇧🇷';  // Brazilian flag (2 code points combined)
echo grapheme_strlen($flag) . PHP_EOL;   // 1 (one grapheme cluster)
print_r(grapheme_str_split($flag));      // ['🇧🇷']

// Family emoji:
$family = '👨‍👩‍👧';  // family (4 emojis combined with ZWJ)
echo grapheme_strlen($family) . PHP_EOL;    // 1
print_r(grapheme_str_split($family));       // ['👨‍👩‍👧']
