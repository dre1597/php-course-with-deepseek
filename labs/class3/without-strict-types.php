<?php

// Without strict_types (default): coercion happens
function sum(int $first, int $second): int
{
    return $first + $second;
}

echo sum(5, "3");      // 8 — string "3" coerced to int 3
echo sum(5, "3.7");    // 8 — float 3.7 → int 3 (precision loss!)
