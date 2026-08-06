<?php
$path = '/var/www/html/index.php';

// Exists?
if (file_exists($path)) {
    echo "The path exists!<br>\n";
}

// Is file?
if (is_file($path)) {
    echo "It's a file!<br>\n";
}

// Is directory?
if (is_dir('/var/www/html')) {
    echo "It's a directory!<br>\n";
}

// Permissions
if (is_readable($path)) {
    echo "Is readable<br>\n";
}

if (is_writable($path)) {
    echo "Is writable<br>\n";
}

// Utility function: validate file before processing
function validateFile(string $path): bool {
    if (!file_exists($path)) {
        echo "Error: file '{$path}' not found.<br>\n";
        return false;
    }
    if (!is_file($path)) {
        echo "Error: '{$path}' is not a file.<br>\n";
        return false;
    }
    if (!is_readable($path)) {
        echo "Error: no read permission.<br>\n";
        return false;
    }
    return true;
}
