<?php

function incrementVisitCounter($file): int
{
    $count = file_exists($file)
        ? (int)file_get_contents($file)
        : 0;

    $count++;

    file_put_contents($file, $count, LOCK_EX);

    return $count;
}
