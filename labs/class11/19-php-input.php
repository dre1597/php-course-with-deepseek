<?php
// Useful for APIs that receive JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON']));
}

echo "Name received: " . ($data['name'] ?? 'not provided');
