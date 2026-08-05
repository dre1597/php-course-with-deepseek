<?php

echo "Searching for number 7...\n";

$numbers = [1, 3, 5, 7, 9, 11, 13];

foreach ($numbers as $position => $number) {
    echo "  Checking position {$position}: {$number}\n";
    if ($number === 7) {
        echo "  → Found at position {$position}!\n";
        break; // Stops the loop immediately
    }
}
/*
Searching for number 7...
  Checking position 0: 1
  Checking position 1: 3
  Checking position 2: 5
  Checking position 3: 7
  → Found at position 3!
*/


$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

$target = 5;
$found = false;

foreach ($matrix as $rowIndex => $row) {
    foreach ($row as $columnIndex => $value) {
        echo "  [{$rowIndex}][{$columnIndex}] = {$value}\n";
        if ($value === $target) {
            echo "  → Found at [{$rowIndex}][{$columnIndex}]\n";
            break 2; // Exits BOTH foreach loops
        }
    }
}


echo "Even numbers between 1 and 10:\n";

for ($i = 1; $i <= 10; $i++) {
    if ($i % 2 !== 0) {
        continue; // Skip odd numbers
    }
    echo "  {$i}\n";
}


$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

foreach ($matrix as $row) {
    foreach ($row as $value) {
        if ($value % 2 === 0) {
            continue 2; // Skip to the NEXT ROW
        }
        echo "{$value} "; // Only prints odd numbers
    }
    echo "\n";
}
// 1 -> next row (2 is even)
// -> next row (4 is even)
// 7 9 -> (8 is even)


// Inside a switch nested in a loop:
$values = [1, 2, 3, 'end']; // keeping 'end' for clarity (used as sentinel)

foreach ($values as $value) {
    if ($value === 'end') {
        break; // Exits foreach
    }

    switch ($value) {
        case 1:
            echo "one\n";
            break; // Exits switch
        case 2:
            echo "two\n";
            continue 2; // Exits switch AND continues foreach (next iteration)
        case 3:
            echo "three — this line never runs\n";
            break;
    }

    echo "After switch (value {$value})\n";
}
/*
Output:
one
After switch (value 1)
two
three — this line never runs
*/
