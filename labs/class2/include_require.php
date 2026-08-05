<?php

include 'header.php';
include 'footer.php';

require 'config/database.php'; // throws error

require_once 'functions.php';
require_once 'functions.php';  // Already included, skipped


// Relative path (relative to current script)
require_once 'config.php';

// Absolute path
require_once '/var/www/app/functions.php';

// Using __DIR__ for safe relative paths
require_once __DIR__ . '/../vendor/autoload.php';

// include inside a function
function loadTemplate(string $name): string
{
    ob_start();
    include __DIR__ . "/templates/{$name}.php";
    return ob_get_clean();
}
