<?php

function filterCsv($source, $destination, $callback): int
{
    $handle = fopen($source, 'r');
    if ($handle === false) {
        return 0;
    }

    $header = fgetcsv($handle, escape: '');
    if ($header === false || (count($header) === 1 && $header[0] === null)) {
        fclose($handle);
        file_put_contents($destination, '');
        return 0;
    }

    $filtered = [];
    while (($row = fgetcsv($handle, escape: '')) !== false) {
        if (count($row) === 1 && $row[0] === null) {
            continue;
        }
        $assoc = array_combine($header, $row);
        if ($callback($assoc)) {
            $filtered[] = $row;
        }
    }
    fclose($handle);

    $out = fopen($destination, 'w');
    fputcsv($out, $header, escape: '');
    foreach ($filtered as $row) {
        fputcsv($out, $row, escape: '');
    }
    fclose($out);

    return count($filtered);
}
