<?php
// upload.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.html');
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    die('No file uploaded.');
}

$file = $_FILES['file'];

// Check upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'The file exceeds the maximum size defined in php.ini (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the maximum size defined in the form (MAX_FILE_SIZE).',
        UPLOAD_ERR_PARTIAL    => 'The upload was only partially completed.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporary directory not found.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
    ];
    $error = $file['error'];
    die('Upload error: ' . ($errorMessages[$error] ?? "Error code {$error}"));
}

// Validate file size (limit: 5 MB)
$maxFileSize = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxFileSize) {
    die('The file is larger than 5 MB.');
}

// Validate MIME type using finfo (more secure than $_FILES['type'])
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($realType, $allowedTypes)) {
    die("File type '{$realType}' not allowed.");
}

// Generate unique name to avoid conflicts
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('img_', true) . '.' . $extension;
$destination = __DIR__ . '/uploads/' . $fileName;

// Ensure directory exists
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Move file from temp location to final destination
if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo "Upload successful!<br>\n";
    echo "Original name: {$file['name']}<br>\n";
    echo "Type: {$realType}<br>\n";
    echo "Size: " . formatFileSize($file['size']) . "<br>\n";
    echo "Saved as: {$fileName}<br>\n";
} else {
    die('Error moving the file.');
}
