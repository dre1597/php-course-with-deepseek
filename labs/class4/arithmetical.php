<?php

$a = 10;
$b = 3;

echo $a + $b;   // 13
echo $a - $b;   // 7
echo $a * $b;   // 30
echo $a / $b;   // 3.3333333333333
echo $a % $b;   // 1
echo $a ** $b;  // 1000 (10³, PHP 5.6+)

$a = 10;
$b = 3;

echo $a + $b;   // 13
echo $a - $b;   // 7
echo $a * $b;   // 30
echo $a / $b;   // 3.3333333333333
echo $a % $b;   // 1
echo $a ** $b;  // 1000 (10³, PHP 5.6+)

$a = 10;

echo -$a;   // -10 — negation
echo +$a;   // 10 — identity (no-op)
echo --$a; // 10 — double negation = original value
