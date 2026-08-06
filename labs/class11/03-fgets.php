<?php
$file = fopen('text.txt', 'r');
while (($line = fgets($file)) !== false) {
    echo rtrim($line) . "<br>\n";
}
fclose($file);
