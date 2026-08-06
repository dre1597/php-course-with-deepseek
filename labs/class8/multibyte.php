<?php

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

echo strlen('café');         // 5 (bytes: depends on encoding)
echo mb_strlen('café');      // 4 (characters)
echo mb_strlen('ação');      // 4
echo mb_strlen('日本語');     // 3

$text = 'Programming in PHP';

echo mb_substr($text, 0, 11);   // Programming
echo mb_substr($text, -3);       // PHP
echo mb_substr($text, 15);       // in PHP

$phrase = 'The explanation is simple';

echo mb_strpos($phrase, 'tion');     // 9 (correct position in characters)
echo mb_strpos($phrase, 'is');       // 14

// Convert case correctly with UTF-8
echo mb_strtoupper('café');              // CAFÉ
echo mb_strtolower('AÇÃO');              // ação

// mb_convert_case with modes:
// MB_CASE_UPPER, MB_CASE_LOWER, MB_CASE_TITLE, MB_CASE_FOLD
echo mb_convert_case('joão da silva', MB_CASE_TITLE, 'UTF-8');
// João Da Silva
