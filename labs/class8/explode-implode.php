<?php

$csv = 'John,Mary,Peter,Anna,Bea';
$names = explode(',', $csv);
print_r($names); // ['John', 'Mary', 'Peter', 'Anna', 'Bea']

// With limit (third parameter):
$phrase = 'one two three four five';
print_r(explode(' ', $phrase, 3));
// ['one', 'two', 'three four five']

// Negative limit — removes the last N elements:
print_r(explode(' ', $phrase, -2));
// ['one', 'two', 'three']

$parts = ['2026', '08', '04'];
echo implode('-', $parts);  // 2026-08-04
echo join('/', $parts);     // 2026/08/04

// Implode without delimiter:
echo implode($parts);      // 20260804

$names = 'anna, charles, BEA, PETER, john';

// Normalize: trim and capitalize each name
$parts = explode(',', $names);
$parts = array_map(fn(string $name): string => mb_ucfirst(mb_strtolower(trim($name))), $parts);
$normalized = implode(', ', $parts);

echo $normalized; // Anna, Charles, Bea, Peter, John
