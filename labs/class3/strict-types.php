<?php

declare(strict_types=1);

function add(int $first, int $second): int
{
    return $first + $second;
}

echo add(5, 3);
echo add(5, "3");     // TypeError! "3" is not int
