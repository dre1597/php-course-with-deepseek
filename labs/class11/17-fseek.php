<?php
$file = fopen('text.txt', 'r');

// Current position (in bytes from the beginning)
$position = ftell($file);
echo "Current position: {$position}<br>\n"; // 0

// Move to byte 10
fseek($file, 10);
echo "Position after fseek: " . ftell($file) . "<br>\n"; // 10

// fseek modes
// SEEK_SET — from the beginning (default)
fseek($file, 5, SEEK_SET);

// SEEK_CUR — from current position
fseek($file, 20, SEEK_CUR); // +20 bytes from current position

// SEEK_END — from the end
fseek($file, -1, SEEK_END); // last byte of the file

// rewind() — back to the beginning (equivalent to fseek($file, 0))
rewind($file);

fclose($file);

// Example: read the last N bytes of a file
function readLastBytes(string $path, int $bytes): string {
    $file = fopen($path, 'r');
    fseek($file, -$bytes, SEEK_END);
    $data = fread($file, $bytes);
    fclose($file);
    return $data;
}
