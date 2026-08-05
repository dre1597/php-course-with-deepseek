<?php

namespace App\Config;

define('APP_NAME', 'MySystem');
define('VERSION', '1.0.0');
define('SESSION_TIME', 3600);

echo APP_NAME;   // MySystem
echo VERSION;     // 1.0.0

// echo $APP_NAME; // Undefined variable — constants don't use $

// define() at runtime

$environment = 'production';

if ($environment === 'production') {
    define('DEBUG_MODE', false);
} else {
    define('DEBUG_MODE', true);
}

var_dump(DEBUG_MODE); // bool(false


const SERVICE_RATE = 0.10;
const PI = 3.14159;

echo SERVICE_RATE * 100 . '%'; // 10%

// const honors the namespace
const APP_NAME = 'MyApp';
// Accessible as \App\Config\APP_NAME

// define is always global
define('APP_VERSION', '2.0');

// define with expression (const doesn't allow this)
define('TIMESTAMP', time());

// define inside a function (const doesn't allow this)
function init(): void
{
    define('INITIALIZED', true);
}

init();
var_dump(defined('INITIALIZED')); // bool(true)
