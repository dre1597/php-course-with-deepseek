<?php

$number = $argv[1] ?? 10;
function getMultiplicationTable($n): void
{
    for ($i = 1; $i <= 10; $i++) {
        echo $n . ' x ' . $i . ' = ' . $n * $i . "\n";
    }
}

getMultiplicationTable($number);
