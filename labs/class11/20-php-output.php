<?php
$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Email', 'Age']);
fputcsv($output, ['John', 'john@email.com', '28']);
fputcsv($output, ['Mary', 'mary@email.com', '34']);
fclose($output);
// This outputs CSV
