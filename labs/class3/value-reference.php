<?php

$first = 10;
$second = $first;   // Copy
$first = 20;
echo $second;       // 10 — independent copy

$first = 10;
$second = &$first;  // Reference (alias)
$first = 20;
echo $second;       // 20 — same zval

$second = 30;
echo $first;        // 30 — both see the change

$prices = [100, 200, 300];

// Without reference — does NOT modify $prices
foreach ($prices as $price) {
    $price *= 1.1;  // Modifies the copy, original untouched
}
print_r($prices);  // [100, 200, 300]

// With reference — modifies in-place
foreach ($prices as &$price) {
    $price *= 1.1;
}
unset($price);      // CRUCIAL: unset after foreach with reference!
print_r($prices);  // [110, 220, 330]
