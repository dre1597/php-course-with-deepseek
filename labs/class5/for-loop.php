<?php

// for (initialization; condition; increment)
for ($i = 0; $i < 5; $i++) {
    echo "i = {$i}\n";
}
// i = 0
// i = 1
// i = 2
// i = 3
// i = 4


// Simultaneous increment and decrement
for ($i = 0, $j = 10; $i <= 10; $i++, $j--) {
    echo "i = {$i}, j = {$j}, sum = " . ($i + $j) . "\n";
}


// No initialization (variable set outside)
$i = 0;
for (; $i < 5; $i++) {
    echo "{$i} ";
}
echo "\n";

// No increment (done inside the block)
for ($i = 0; $i < 5;) {
    echo "{$i} ";
    $i++;
}
echo "\n";

// Infinite loop with for (all expressions empty)
// for (;;) { ... } — equivalent to while(true) { ... }

// No condition (infinite loop — useful with internal break)
$i = 0;
for (; ;) {
    if ($i >= 5) {
        break;
    }
    echo "{$i} ";
    $i++;
}
// 0 1 2 3 4


echo "Multiplication table for 7:\n";
echo str_repeat('─', 20) . "\n";

for ($multiplier = 1; $multiplier <= 10; $multiplier++) {
    $result = 7 * $multiplier;
    echo "7 × " . str_pad($multiplier, 2, ' ', STR_PAD_LEFT) . " = " . str_pad($result, 2, ' ', STR_PAD_LEFT) . "\n";
}
/*
Multiplication table for 7:
────────────────────
7 ×  1 =  7
7 ×  2 = 14
7 ×  3 = 21
...
7 × 10 = 70
*/


$months = [
    'January', 'February', 'March', 'April',
    'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December',
];

echo "<select name='mes'>\n";
for ($i = 0; $i < count($months); $i++) {
    $value = $i + 1;
    $selected = ($value === (int)date('m')) ? ' selected' : '';
    echo "    <option value='{$value}'{$selected}>{$months[$i]}</option>\n";
}
echo "</select>\n";
