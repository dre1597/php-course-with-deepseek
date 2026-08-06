<?php
$file = 'text.txt';

// Last access (Unix timestamp)
$accessed = fileatime($file);
echo "Last access: " . date('Y-m-d H:i:s', $accessed) . "<br>\n";

// Last modification
$modified = filemtime($file);
echo "Last modification: " . date('Y-m-d H:i:s', $modified) . "<br>\n";

// Size in bytes
$fileSize = filesize($file);
echo "Size: {$fileSize} bytes<br>\n";

// Human-readable size format
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

echo "Formatted size: " . formatFileSize($fileSize) . "<br>\n";
