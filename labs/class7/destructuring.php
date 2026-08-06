<?php

$coordinates = [10, 20, 30];

[$coordX, $coordY, $coordZ] = $coordinates;
echo "x={$coordX}, y={$coordY}, z={$coordZ}"; // x=10, y=20, z=30

// Skip elements:
[,, $third] = $coordinates;
echo $third; // 30

$user = [
    'name'    => 'Charles',
    'email'   => 'charles@email.com',
    'city'    => 'São Paulo',
];

['name' => $name, 'email' => $email] = $user;
echo $name;  // Charles
echo $email; // charles@email.com

$students = [
    ['id' => 1, 'name' => 'Anna',  'grade' => 9.5],
    ['id' => 2, 'name' => 'John',  'grade' => 7.0],
    ['id' => 3, 'name' => 'Bea',   'grade' => 8.5],
];

foreach ($students as ['name' => $name, 'grade' => $grade]) {
    echo "{$name}: {$grade}" . PHP_EOL;
}
// Anna: 9.5
// John: 7.0
// Bea: 8.5
