<?php
$file = fopen('data.csv', 'r');

$header = fgetcsv($file);

while (($line = fgetcsv($file)) !== false) {
    $record = array_combine($header, $line);
    echo "Name: {$record['name']}, Email: {$record['email']}<br>\n";
}
fclose($file);

$file = fopen('data.tsv', 'r');
while (($line = fgetcsv($file, 0, "\t")) !== false) {
    print_r($line);
}
fclose($file);
