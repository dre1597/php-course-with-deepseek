<?php
// Simple creation
mkdir('new-folder');

// Create recursively with specific permissions
mkdir('project/src/controllers', 0755, true);

// Check before creating
$directory = 'uploads/2024';
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
    echo "Directory '{$directory}' created.<br>\n";
}

$directory = 'temp';

if (is_dir($directory)) {
    rmdir($directory);
    echo "Directory '{$directory}' removed.<br>\n";
}

// Function to remove directory with all contents
function removeDirectory(string $path): bool {
    if (!is_dir($path)) {
        return false;
    }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            removeDirectory($fullPath);
        } else {
            unlink($fullPath);
        }
    }
    return rmdir($path);
}
