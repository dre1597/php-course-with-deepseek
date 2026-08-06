<?php
$lines = file('text.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $number => $line) {
    echo ($number + 1) . ": {$line}<br>\n";
}

// Count lines quickly
echo "Total lines: " . count(file('text.txt'));

// Useful for forcing file downloads
$file = 'document.pdf';

if (file_exists($file)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
