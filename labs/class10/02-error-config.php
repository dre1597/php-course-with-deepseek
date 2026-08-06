<?php

// === 02 — Error Display Configuration ===

// error_reporting()
// Development environment: show EVERYTHING
error_reporting(E_ALL);

// Production environment: hide notices, warnings and deprecated
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Only fatal errors
error_reporting(E_ERROR);

// Turn off (NOT recommended!)
error_reporting(0);

// ini_set() — Runtime Configuration
// SHOW errors on screen (development)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// HIDE errors from screen (production) — but log to file
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php/php_errors.log');

// === Ideal Configuration per Environment ===

// ====================
// DEVELOPMENT
// ====================
function setupDevEnvironment(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '0');
}

// ====================
// PRODUCTION
// ====================
function setupProductionEnvironment(): void
{
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Detect by hostname or environment variable
if (getenv('APP_ENV') === 'production') {
    setupProductionEnvironment();
} else {
    setupDevEnvironment();
}

// Also configurable in php.ini:
//
// ; php.ini — Development
// error_reporting = E_ALL
// display_errors = On
// display_startup_errors = On
// log_errors = Off
//
// ; php.ini — Production
// error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
// display_errors = Off
// display_startup_errors = Off
// log_errors = On
// error_log = /var/log/php/error.log
