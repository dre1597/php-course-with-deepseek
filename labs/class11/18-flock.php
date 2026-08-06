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

// LOCK_SH — Shared lock (read). Multiple processes can obtain simultaneously.
flock($file, LOCK_SH);

// LOCK_EX — Exclusive lock (write). Only one process at a time.
flock($file, LOCK_EX);

// LOCK_UN — Releases the lock.
flock($file, LOCK_UN);

// LOCK_NB — Non-blocking. Returns immediately if lock can't be obtained.
if (!flock($file, LOCK_EX | LOCK_NB)) {
    echo "File is busy right now.<br>\n";
}
