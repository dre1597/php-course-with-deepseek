<?php

$data = [
    'name'  => 'Beatrice',
    'age'   => 28,
    'city'  => 'New York',
    'hobbies' => ['reading', 'music', 'running'],
];

print_r($data);

// Return as string instead of displaying
$text = print_r($data, true);
