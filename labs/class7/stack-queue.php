<?php

$stack = ['A', 'B'];
array_push($stack, 'C', 'D', 'E');

print_r($stack); // ['A', 'B', 'C', 'D', 'E']

// Faster equivalent for single element:
$stack[] = 'F';

$stack = ['A', 'B', 'C'];
$last = array_pop($stack);  // remove and return 'C'

echo $last;                 // C
print_r($stack);              // ['A', 'B']

$queue = ['first', 'second', 'third'];
$served = array_shift($queue);  // remove and return 'first'

echo $served;                   // first
print_r($queue);                   // ['second', 'third']

$queue = ['B', 'C'];
array_unshift($queue, 'A');

print_r($queue); // ['A', 'B', 'C']
