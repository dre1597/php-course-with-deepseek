<?php
// Simple read
$content = file_get_contents('text.txt');
if ($content === false) {
    die('Error reading the file.');
}
echo nl2br($content);

// URL read (if allow_url_fopen is enabled)
$html = file_get_contents('https://www.example.com');

// Stream context read (custom headers)
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: PHP Script\r\n"
    ]
]);
$html = file_get_contents('https://api.example.com/data', false, $context);
