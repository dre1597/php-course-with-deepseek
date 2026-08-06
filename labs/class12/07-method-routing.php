<?php
// GET: search, list, filter — does not change state
// POST: create, update, delete — changes state

// Example of method-based routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && $path === '/users') {
    // List users
} elseif ($method === 'POST' && $path === '/users') {
    // Create user
}
