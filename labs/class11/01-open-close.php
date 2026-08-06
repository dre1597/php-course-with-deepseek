<?php
// Mode 'r' — read only
$file = fopen('data.txt', 'r');
if ($file === false) {
    die('Could not open the file.');
}
// Work with the file...
fclose($file);

// Mode 'w' — write (overwrites)
$file = fopen('log.txt', 'w');
fwrite($file, "Line 1\n");
fwrite($file, "Line 2\n");
fclose($file);

// Mode 'a' — append to end
$file = fopen('log.txt', 'a');
fwrite($file, "Line 3 (append)\n");
fclose($file);

// Mode 'r+' — read and write (file must exist)
$file = fopen('log.txt', 'r+');
$content = fread($file, 1024);
fwrite($file, "\nAdded via r+\n");
fclose($file);

$file = fopen('data.txt', 'r');
// ... operations ...
fclose($file); // releases the resource
