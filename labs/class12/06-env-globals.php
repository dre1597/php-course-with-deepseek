<?php
// Defined in .env, docker-compose, or the operating system
// Example: export APP_DEBUG=true && php script.php

$debugMode = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
if ($debugMode) {
    echo "Debug mode active<br>\n";
}

// Database password NEVER in the code
$dbPassword = $_ENV['DB_PASSWORD'] ?? '';

$config = ['app' => 'MyApp', 'version' => '3.0'];
$dbHost = 'localhost';

// $GLOBALS contains ALL global variables
echo $GLOBALS['config']['app']; // MyApp
echo $GLOBALS['dbHost'];        // localhost

// It also contains superglobals
print_r($GLOBALS['_SERVER']);
