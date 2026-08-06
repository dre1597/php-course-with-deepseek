<?php
// Simple write (overwrites)
file_put_contents('file.txt', 'File contents');

// Append (adds to the end)
file_put_contents('file.txt', "\nMore content", FILE_APPEND);

// With LOCK_EX to prevent race conditions
file_put_contents('file.txt', 'Secure content', LOCK_EX);

// Combining flags
file_put_contents('file.txt', "Content\n", FILE_APPEND | LOCK_EX);

// Check return value
$result = file_put_contents('data.json', json_encode(['name' => 'John']));
if ($result === false) {
    die('Error writing to the file.');
}
echo "{$result} bytes written";
