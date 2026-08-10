<?php

const MAX_UPLOAD_SIZE = 2 * 1024 * 1024;

function processUploads(array $files, string $uploadDir, string $metadataFile, int $maxSize = MAX_UPLOAD_SIZE): array
{
    $uploaded = [];
    $errors = [];

    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        $errorCode = $files['error'][$i];

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[$i] = "Upload error code: $errorCode";
            continue;
        }

        $tmpName = $files['tmp_name'][$i];
        $name = $files['name'][$i];
        $size = $files['size'][$i];

        $mime = mime_content_type($tmpName);
        if (!str_starts_with($mime, 'image/')) {
            $errors[$i] = "Invalid file type '$mime'. Only images are allowed.";
            continue;
        }

        if ($size > $maxSize) {
            $errors[$i] = "File '$name' exceeds the maximum size of $maxSize bytes.";
            continue;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $savedName = uniqid();
        if ($extension !== '') {
            $savedName .= '.' . $extension;
        }

        copy($tmpName, $uploadDir . $savedName);

        $entry = [
            'original_name' => $name,
            'saved_name' => $savedName,
            'type' => $mime,
            'size' => $size,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        $uploaded[] = $entry;
        appendMetadataEntry($metadataFile, $entry);
    }

    return [
        'uploaded' => $uploaded,
        'errors' => $errors,
    ];
}

function appendMetadataEntry(string $metadataFile, array $entry): void
{
    $existing = [];
    if (file_exists($metadataFile)) {
        $content = file_get_contents($metadataFile);
        $existing = json_decode($content, true) ?: [];
    }

    $existing[] = $entry;
    file_put_contents($metadataFile, json_encode($existing, JSON_PRETTY_PRINT), LOCK_EX);
}
