# Module 11: File Handling

## Overview

PHP offers a robust set of functions for manipulating files on the server filesystem. In this module, you'll learn how to open, read, write, move, rename, and delete files, as well as working with uploads and streams.

---

## 1. Opening and Closing Files: `fopen()` and `fclose()`

The `fopen()` function is the starting point for working with files in PHP. It returns a **resource** (or `false` on error).

### Opening modes

| Mode | Description |
|------|-----------|
| `r`  | Read. Pointer at the beginning. File must exist. |
| `r+` | Read and write. Pointer at the beginning. File must exist. |
| `w`  | Write. Creates/truncates the file. Pointer at the beginning. |
| `w+` | Read and write. Creates/truncates the file. |
| `a`  | Write. Creates if it doesn't exist. Pointer at the end. |
| `a+` | Read and write. Creates if it doesn't exist. Pointer at the end. |

```php
<?php
// Mode 'r' — read only
$file = fopen('data.txt', 'r');
if ($file === false) {
    die('Could not open the file.');
}
// Work with the file...
fclose($file);

// Mode 'w' — write (overwrites)
$file = fopen('log.txt', 'w');
fwrite($file, "Line 1\n");
fwrite($file, "Line 2\n");
fclose($file);

// Mode 'a' — append to end
$file = fopen('log.txt', 'a');
fwrite($file, "Line 3 (append)\n");
fclose($file);

// Mode 'r+' — read and write (file must exist)
$file = fopen('log.txt', 'r+');
$content = fread($file, 1024);
fwrite($file, "\nAdded via r+\n");
fclose($file);
```

### `fclose()` — Always close the file

```php
<?php
$file = fopen('data.txt', 'r');
// ... operations ...
fclose($file); // releases the resource
```

> **Tip:** If you forget to call `fclose()`, PHP closes it at the end of the script. But it's good practice to close explicitly, especially when working with locks.

---

## 2. Reading Files

### `fread()` — Reading by bytes

```php
<?php
$file = fopen('text.txt', 'r');
$content = fread($file, filesize('text.txt'));
fclose($file);
echo $content;
```

### `fgets()` — Line by line reading

```php
<?php
$file = fopen('text.txt', 'r');
while (($line = fgets($file)) !== false) {
    echo rtrim($line) . "<br>\n";
}
fclose($file);
```

### `fgetcsv()` — Reading CSV

```php
<?php
$file = fopen('data.csv', 'r');

$header = fgetcsv($file);

while (($line = fgetcsv($file)) !== false) {
    $record = array_combine($header, $line);
    echo "Name: {$record['name']}, Email: {$record['email']}<br>\n";
}
fclose($file);
```

Suppose `data.csv` contains:

```
name,email,age
John,john@email.com,28
Mary,mary@email.com,34

```

Output:

```
Name: John, Email: john@email.com
Name: Mary, Email: mary@email.com

```

### `fgetcsv()` with custom delimiter

```php
<?php
$file = fopen('data.tsv', 'r');
while (($line = fgetcsv($file, 0, "\t")) !== false) {
    print_r($line);
}
fclose($file);
```

### `fwrite()` — Writing

```php
<?php
$file = fopen('output.txt', 'w');
fwrite($file, "First line\n");
fwrite($file, "Second line\n");

$lines = ['Line 3', 'Line 4', 'Line 5'];
foreach ($lines as $line) {
    fwrite($file, $line . "\n");
}
fclose($file);
```

---

## 3. `file_get_contents()` and `file_put_contents()`

These functions dramatically simplify reading and writing entire files.

### `file_get_contents()` — Read the whole file into a string

```php
<?php
// Simple read
$content = file_get_contents('text.txt');
if ($content === false) {
    die('Error reading the file.');
}
echo nl2br($content);

// URL read (if allow_url_fopen is enabled)
$html = file_get_contents('https://www.example.com');

// Stream context read (custom headers)
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: PHP Script\r\n"
    ]
]);
$html = file_get_contents('https://api.example.com/data', false, $context);
```

### `file_put_contents()` — Write a string to a file

```php
<?php
// Simple write (overwrites)
file_put_contents('file.txt', 'File contents');

// Append (adds to the end)
file_put_contents('file.txt', "\nMore content", FILE_APPEND);

// With LOCK_EX to prevent race conditions
file_put_contents('file.txt', 'Secure content', LOCK_EX);

// Combining flags
file_put_contents('file.txt', "Content\n", FILE_APPEND | LOCK_EX);

// Check return value
$result = file_put_contents('data.json', json_encode(['name' => 'John']));
if ($result === false) {
    die('Error writing to the file.');
}
echo "{$result} bytes written";
```

> **Tip:** `file_get_contents()` and `file_put_contents()` are convenient shortcuts. For very large files, prefer `fopen()` + incremental reading to avoid memory issues.

---

## 4. File and Directory Checks

```php
<?php
$path = '/var/www/html/index.php';

// Exists?
if (file_exists($path)) {
    echo "The path exists!<br>\n";
}

// Is file?
if (is_file($path)) {
    echo "It's a file!<br>\n";
}

// Is directory?
if (is_dir('/var/www/html')) {
    echo "It's a directory!<br>\n";
}

// Permissions
if (is_readable($path)) {
    echo "Is readable<br>\n";
}

if (is_writable($path)) {
    echo "Is writable<br>\n";
}

// Utility function: validate file before processing
function validateFile(string $path): bool {
    if (!file_exists($path)) {
        echo "Error: file '{$path}' not found.<br>\n";
        return false;
    }
    if (!is_file($path)) {
        echo "Error: '{$path}' is not a file.<br>\n";
        return false;
    }
    if (!is_readable($path)) {
        echo "Error: no read permission.<br>\n";
        return false;
    }
    return true;
}
```

---

## 5. `file()` and `readfile()`

### `file()` — Read file into an array (each element = one line)

```php
<?php
$lines = file('text.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $number => $line) {
    echo ($number + 1) . ": {$line}<br>\n";
}

// Count lines quickly
echo "Total lines: " . count(file('text.txt'));
```

### `readfile()` — Read and send to output buffer

```php
<?php
// Useful for forcing file downloads
$file = 'document.pdf';

if (file_exists($file)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
```

---

## 6. File Metadata

```php
<?php
$file = 'text.txt';

// Last access (Unix timestamp)
$accessed = fileatime($file);
echo "Last access: " . date('Y-m-d H:i:s', $accessed) . "<br>\n";

// Last modification
$modified = filemtime($file);
echo "Last modification: " . date('Y-m-d H:i:s', $modified) . "<br>\n";

// Size in bytes
$fileSize = filesize($file);
echo "Size: {$fileSize} bytes<br>\n";

// Human-readable size format
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

echo "Formatted size: " . formatFileSize($fileSize) . "<br>\n";
```

---

## 7. File Operations: `unlink()`, `rename()`, `copy()`

### `unlink()` — Delete file

```php
<?php
$file = 'temporary.txt';

if (file_exists($file)) {
    if (unlink($file)) {
        echo "File deleted successfully.";
    } else {
        echo "Error deleting the file.";
    }
}
```

> **Warning:** `unlink()` permanently deletes the file — there's no recycle bin. Always check before deleting.

### `rename()` — Rename or move

```php
<?php
// Rename
rename('old.txt', 'new.txt');

// Move to another directory
rename('document.txt', 'backup/document.txt');

// Move with different name
rename('draft.txt', 'finished/draft-2024.txt');
```

### `copy()` — Copy file

```php
<?php
$source = 'original-photo.jpg';
$destination = 'backup/photo-copy.jpg';

if (copy($source, $destination)) {
    echo "File copied successfully.";
} else {
    echo "Error copying the file.";
}
```

---

## 8. Directories: `mkdir()` and `rmdir()`

### `mkdir()` — Create directory

```php
<?php
// Simple creation
mkdir('new-folder');

// Create recursively with specific permissions
mkdir('project/src/controllers', 0755, true);

// Check before creating
$directory = 'uploads/2024';
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
    echo "Directory '{$directory}' created.<br>\n";
```

### `rmdir()` — Remove directory (must be empty)

```php
<?php
$directory = 'temp';

if (is_dir($directory)) {
    rmdir($directory);
    echo "Directory '{$directory}' removed.<br>\n";
}

// Function to remove directory with all contents
function removeDirectory(string $path): bool {
    if (!is_dir($path)) {
        return false;
    }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            removeDirectory($fullPath);
        } else {
            unlink($fullPath);
        }
    }
    return rmdir($path);
}
```

---

## 9. Listing Contents: `scandir()` and `glob()`

### `scandir()` — List files and directories

```php
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
```

### `glob()` — Pattern matching (wildcard)

```php
<?php
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
```

---

## 10. Path Information: `pathinfo()`, `basename()`, `dirname()`, `realpath()`

```php
<?php
$file = '/var/www/html/images/profile-photo.jpg';

// pathinfo() — full path information
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
```

---

## 11. File Upload with `$_FILES`

### HTML Form

```html
<!-- upload.html -->
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" accept="image/*" required>
    <input type="text" name="description" placeholder="Image description">
    <button type="submit">Upload</button>
</form>
```

### Upload Processing

```php
<?php
// upload.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.html');
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    die('No file uploaded.');
}

$file = $_FILES['file'];

// Check upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'The file exceeds the maximum size defined in php.ini (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the maximum size defined in the form (MAX_FILE_SIZE).',
        UPLOAD_ERR_PARTIAL    => 'The upload was only partially completed.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporary directory not found.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
    ];
    $error = $file['error'];
    die('Upload error: ' . ($errorMessages[$error] ?? "Error code {$error}"));
}

// Validate file size (limit: 5 MB)
$maxFileSize = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxFileSize) {
    die('The file is larger than 5 MB.');
}

// Validate MIME type using finfo (more secure than $_FILES['type'])
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($realType, $allowedTypes)) {
    die("File type '{$realType}' not allowed.");
}

// Generate unique name to avoid conflicts
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('img_', true) . '.' . $extension;
$destination = __DIR__ . '/uploads/' . $fileName;

// Ensure directory exists
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Move file from temp location to final destination
if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo "Upload successful!<br>\n";
    echo "Original name: {$file['name']}<br>\n";
    echo "Type: {$realType}<br>\n";
    echo "Size: " . formatFileSize($file['size']) . "<br>\n";
    echo "Saved as: {$fileName}<br>\n";
} else {
    die('Error moving the file.');
}
```

> **Warning:** Never trust `$_FILES['file']['type']` — it's sent by the client. Always validate the real type using `finfo`.

### Multiple File Upload

```php
<?php
// Form: <input type="file" name="photos[]" multiple>

function uploadMultiple(array $photos, string $targetDir, array $allowedTypes, int $maxFileSize): array {
    $uploadedFiles = [];

    for ($i = 0; $i < count($photos['name']); $i++) {
        if ($photos['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // skip files with errors
        }

        if ($photos['size'][$i] > $maxFileSize) {
            continue;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realType = finfo_file($finfo, $photos['tmp_name'][$i]);
        finfo_close($finfo);

        if (!in_array($realType, $allowedTypes)) {
            continue;
        }

        $extension = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
        $fileName = uniqid('photo_') . '_' . time() . '.' . $extension;
        $destination = $targetDir . '/' . $fileName;

        if (move_uploaded_file($photos['tmp_name'][$i], $destination)) {
            $uploadedFiles[] = [
                'original_name' => $photos['name'][$i],
                'final_name'    => $fileName,
                'type'          => $realType,
                'size'          => $photos['size'][$i],
            ];
        }
    }

    return $uploadedFiles;
}
```

### Relevant PHP Ini Settings for Upload

```ini
; php.ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20
upload_tmp_dir = /tmp/php-uploads

```

> **Tip:** `post_max_size` should be larger than `upload_max_filesize`, since the POST includes other form fields besides the file.

---

## 12. File Positioning: `fseek()`, `ftell()`, `rewind()`

```php
<?php
$file = fopen('text.txt', 'r');

// Current position (in bytes from the beginning)
$position = ftell($file);
echo "Current position: {$position}<br>\n"; // 0

// Move to byte 10
fseek($file, 10);
echo "Position after fseek: " . ftell($file) . "<br>\n"; // 10

// fseek modes
// SEEK_SET — from the beginning (default)
fseek($file, 5, SEEK_SET);

// SEEK_CUR — from current position
fseek($file, 20, SEEK_CUR); // +20 bytes from current position

// SEEK_END — from the end
fseek($file, -1, SEEK_END); // last byte of the file

// rewind() — back to the beginning (equivalent to fseek($file, 0))
rewind($file);

fclose($file);

// Example: read the last N bytes of a file
function readLastBytes(string $path, int $bytes): string {
    $file = fopen($path, 'r');
    fseek($file, -$bytes, SEEK_END);
    $data = fread($file, $bytes);
    fclose($file);
    return $data;
}
```

---

## 13. File Locking: `flock()`

```php
<?php
// Safe write with exclusive lock
$file = fopen('counter.txt', 'c+'); // 'c+' opens for read/write, creates if it doesn't exist

if (flock($file, LOCK_EX)) { // Exclusive lock
    $counter = (int) fread($file, 1024);
    $counter++;

    rewind($file);
    ftruncate($file, 0); // clear the file
    fwrite($file, (string) $counter);

    flock($file, LOCK_UN); // Release the lock
} else {
    echo "Could not get lock.<br>\n";
}

fclose($file);
echo "Counter: {$counter}<br>\n";
```

### Lock Types

```php
<?php
// LOCK_SH — Shared lock (read). Multiple processes can obtain simultaneously.
flock($file, LOCK_SH);

// LOCK_EX — Exclusive lock (write). Only one process at a time.
flock($file, LOCK_EX);

// LOCK_UN — Releases the lock.
flock($file, LOCK_UN);

// LOCK_NB — Non-blocking. Returns immediately if lock can't be obtained.
if (!flock($file, LOCK_EX | LOCK_NB)) {
    echo "File is busy right now.<br>\n";
```

> **Warning:** `flock()` only works with advisory locking (Linux, macOS). Windows behavior may vary. `flock()` doesn't work with some remote stream wrappers.

---

## 14. Streams and Wrappers

### `php://input` — Read raw request body

```php
<?php
// Useful for APIs that receive JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON']));
}

echo "Name received: " . ($data['name'] ?? 'not provided');
```

### `php://output` — Write to output

```php
<?php
$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Email', 'Age']);
fputcsv($output, ['John', 'john@email.com', '28']);
fputcsv($output, ['Mary', 'mary@email.com', '34']);
fclose($output);
// This outputs CSV
```

### `php://memory` and `php://temp` — In-memory files

```php
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
```

### Wrappers: reading files from different sources

```php
<?php
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
```

---

## 15. Practical Example: Logging System

```php
<?php
class Logger {
    private string $file;

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $line = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }

    public function read(int $lines = 50, string $level = null): array {
        $logs = [];
        $file = fopen($this->file, 'r');
        if ($file === false) {
            return $logs;
        }

        while (($line = fgets($file)) !== false) {
            if ($level !== null && !str_contains($line, $level . ':')) {
                continue;
            }
            $logs[] = rtrim($line);
        }
        fclose($file);

        return array_slice($logs, -$lines);
    }

    public function clear(): bool {
        return file_put_contents($this->file, '') !== false;
    }
}

// Usage
$logger = new Logger(__DIR__ . '/app.log');
$logger->info('System started', ['version' => '2.0']);
$logger->error('Database connection failed', ['error' => 'timeout']);
print_r($logger->read(10, 'ERROR'));
```

---

## 16. `request_parse_body()` (PHP 8.4+)

> **PHP 8.4+**

The new `request_parse_body()` function allows processing the request body programmatically, useful for APIs receiving data in formats like JSON.

```php
<?php
// PHP 8.4+ — alternative to $_POST for APIs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = request_parse_body();

    // For JSON, combine with php://input
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $json = file_get_contents('php://input');
        $result = json_decode($json, true);
    }

    print_r($result);
}
```

---

## 🔗 Navegação

- [← Module 10: Error Handling](./10-tratamento-de-erros.md)
- [→ Module 12: Forms and Superglobals](./12-formularios-e-superglobais.md)

## References

- [PHP: Funções de Sistema de Arquivos](https://www.php.net/manual/en/book.filesystem.php)
- [PHP: fopen](https://www.php.net/manual/en/function.fopen.php)
- [PHP: Handling File Uploads](https://www.php.net/manual/en/features.file-upload.php)
- [PHP: finfo — Fileinfo](https://www.php.net/manual/en/book.fileinfo.php)
- [PHP: Streams](https://www.php.net/manual/en/book.stream.php)
- [PHP: flock](https://www.php.net/manual/en/function.flock.php)
