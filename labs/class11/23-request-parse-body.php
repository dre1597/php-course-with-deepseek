<?php
// PHP 8.4+ — alternative to $_POST for APIs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = request_parse_body();

    // For JSON, combine with php://input
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $json = file_get_contents('php://input');
        $result = json_decode($json, true);
    }

    print_r($result);
}
