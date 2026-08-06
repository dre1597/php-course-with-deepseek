<?php
$file = fopen('text.txt', 'r');
$content = fread($file, filesize('text.txt'));
fclose($file);
echo $content;
