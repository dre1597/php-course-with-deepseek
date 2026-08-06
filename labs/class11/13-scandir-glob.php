<?php
$items = scandir('/var/www/html');

echo "<ul>\n";
foreach ($items as $item) {
    if ($item === '.' || $item === '..') {
        continue;
    }
    $type = is_dir($item) ? '[DIR]' : '[FILE]';
    echo "<li>{$type} {$item}</li>\n";
}
echo "</ul>\n";

// Reverse sort
$items = scandir('/var/www/html', SCANDIR_SORT_DESCENDING);

// All .php files in the directory
$filesPHP = glob('*.php');
print_r($filesPHP);

// All .txt files in subdirectories (recursive with **)
$filesTXT = glob('**/*.txt');

// Search multiple patterns
$images = glob('*.{jpg,jpeg,png,gif}', GLOB_BRACE);

foreach ($images as $image) {
    echo "<img src='{$image}' alt='Image'><br>\n";
}

// Practical example: list files from uploads directory
function listUploads(string $dir): array {
    if (!is_dir($dir)) {
        return [];
    }
    return glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
}
