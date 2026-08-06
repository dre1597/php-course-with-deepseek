<?php

// Old syntax (avoid):
$list = array(1, 2, 3);

// Modern syntax (preferred):
$list = [1, 2, 3];

$items = ['a', 'b', 7 => 'c', 'd', 'e'];

print_r($items);
/*
Array
(
    [0] => a
    [1] => b
    [7] => c
    [8] => d
    [9] => e
)
*/
