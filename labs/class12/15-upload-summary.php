<?php
// See Module 11 for full details

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];

    $tmpName      = $file['tmp_name'];
    $originalName = $file['name'];
    $fileSize     = $file['size'];
    $error        = $file['error'];

    if ($error === UPLOAD_ERR_OK) {
        $destination = __DIR__ . '/uploads/' . basename($originalName);
        move_uploaded_file($tmpName, $destination);
        echo "Upload complete!";
    } else {
        echo "Upload error: code {$error}";
    }
}
