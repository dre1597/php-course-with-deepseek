<?php
$file = '/var/www/html/images/profile-photo.jpg';

// pathinfo() — informações completas do caminho
$info = pathinfo($file);
echo "Directory: {$info['dirname']}<br>\n";     // /var/www/html/images
echo "Base name: {$info['basename']}<br>\n";    // profile-photo.jpg
echo "Extension: {$info['extension']}<br>\n";    // jpg
echo "Name: {$info['filename']}<br>\n";         // profile-photo

// Or retrieve specific parts via constant
echo pathinfo($file, PATHINFO_EXTENSION);    // jpg
echo pathinfo($file, PATHINFO_FILENAME);     // profile-photo
echo pathinfo($file, PATHINFO_DIRNAME);      // /var/www/html/images
echo pathinfo($file, PATHINFO_BASENAME);     // profile-photo.jpg

// basename() — just the file name
echo basename('/var/www/html/index.php');        // index.php
echo basename('/var/www/html/index.php', '.php'); // index (remove extension)

// dirname() — just the directory
echo dirname('/var/www/html/index.php');         // /var/www/html
echo dirname('/var/www/html');                   // /var/www

// realpath() — resolved absolute path (resolves . and .., symlinks)
echo realpath('./file.txt');                     // /var/www/html/file.txt
echo realpath('../../etc/passwd');               // /etc/passwd

// Practical example: sanitize uploaded file name
function sanitizeFileName(string $originalName): string {
    $info = pathinfo($originalName);
    $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
    $extension = strtolower($info['extension'] ?? '');
    return $sanitizedName . '.' . $extension;
}

echo sanitizeFileName('My Photo (2024)!!!.JPG'); // My_Photo__2024____.jpg
