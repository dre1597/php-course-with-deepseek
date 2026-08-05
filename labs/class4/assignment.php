<?php

$a = 10;

$a += 5;     // $a = $a + 5    → 15
$a -= 3;     // $a = $a - 3    → 12
$a *= 2;     // $a = $a * 2    → 24
$a /= 4;     // $a = $a / 4    → 6
$a %= 4;     // $a = $a % 4    → 2
$a **= 3;    // $a = $a ** 3   → 8
$a .= "abc"; // $a = $a . "abc" → "8abc" (string concatenation)

$a = $b = $c = 42;
echo $a; // 42
echo $b; // 42
echo $c; // 42

// Right-to-left evaluation
$i = 1;
$j = ($i += 5) * 2;  // $i becomes 6, $j = 6 * 2 = 12
echo "i={$i}, j={$j}"; // i=6, j=12
