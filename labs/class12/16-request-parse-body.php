<?php
// PHP 8.4+
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$postData, $fileData] = request_parse_body();

    // $postData contains form fields (equivalent to $_POST)
    // $fileData contains uploaded files (equivalent to $_FILES)

    $name = $postData['name'] ?? '';

    if (isset($fileData['document'])) {
        $file = $fileData['document'];
        move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $file['name']);
    }
}
