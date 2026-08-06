<?php
// php://memory — stores everything in RAM
$memory = fopen('php://memory', 'r+');
fwrite($memory, "Temporary data in memory\n");
fwrite($memory, "Nothing is written to disk\n");

rewind($memory);
echo fread($memory, 1024);
fclose($memory);

// php://temp — stores in RAM up to 2MB, then uses disk
$temp = fopen('php://temp', 'r+');
for ($i = 0; $i < 1000; $i++) {
    fwrite($temp, "Line {$i}\n");
}
rewind($temp);
echo stream_get_contents($temp);
fclose($temp);

// Local files
$local = file_get_contents('/path/file.txt');

// HTTP URLs (if allow_url_fopen = On)
$remote = file_get_contents('https://jsonplaceholder.typicode.com/todos/1');

// FTP
// $ftp = file_get_contents('ftp://user:password@server/file.txt');

// Standard input read (terminal)
$stdin = file_get_contents('php://stdin');

// Data sent via traditional POST
$post = file_get_contents('php://input');
