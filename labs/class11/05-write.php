<?php
$file = fopen('output.txt', 'w');
fwrite($file, "First line\n");
fwrite($file, "Second line\n");

$lines = ['Line 3', 'Line 4', 'Line 5'];
foreach ($lines as $line) {
    fwrite($file, $line . "\n");
}
fclose($file);
