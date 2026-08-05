<?php
// prepare_data.php — entry point (run this file)
declare(strict_types=1);

$title = 'Home';
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob',   'email' => 'bob@example.com'],
];

require 'template.php';
