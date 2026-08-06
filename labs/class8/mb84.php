<?php

$text = "　こんにちは　";  // ideographic spaces (U+3000)

// trim() does NOT remove multibyte characters
echo trim($text);              // "　こんにちは　" (unchanged)

// mb_trim() removes correctly
echo mb_trim($text);           // "こんにちは"

// With specific characters:
$value = '...$ 99.90...';
echo mb_trim($value, '.');      // "$ 99.90"

// ltrim and rtrim
echo mb_ltrim('   left spaces', ' ');    // "left spaces"
echo mb_rtrim('right spaces   ', ' ');    // "right spaces"

// PHP < 8.4: ucfirst did not handle accents
echo ucfirst('árvore');              // árvore (no change!)

// PHP 8.4+: mb_ucfirst converts correctly
echo mb_ucfirst('árvore');           // Árvore
echo mb_ucfirst('último dia');       // Último dia

// mb_lcfirst
echo mb_lcfirst('HELLO');            // hELLO
echo mb_lcfirst('ÁRVORE');           // áRVORE

// Application: capitalize city name
$city = 'são paulo';
echo mb_ucfirst($city);            // São paulo
echo mb_convert_case($city, MB_CASE_TITLE, 'UTF-8'); // São Paulo
