<?php
$file = 'temporary.txt';

if (file_exists($file)) {
    if (unlink($file)) {
        echo "File deleted successfully.";
    } else {
        echo "Error deleting the file.";
    }
}

// Rename
rename('old.txt', 'new.txt');

// Move to another directory
rename('document.txt', 'backup/document.txt');

// Move with different name
rename('draft.txt', 'finished/draft-2024.txt');

$source = 'original-photo.jpg';
$destination = 'backup/photo-copy.jpg';

if (copy($source, $destination)) {
    echo "File copied successfully.";
} else {
    echo "Error copying the file.";
}
