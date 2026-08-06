<?php

// === 10 — Backtraces in Fatal Errors — PHP 8.5+ ===

function processData(): void
{
    nonExistentFunction();  // fatal error
}

function executeTask(): void
{
    processData();
}

executeTask();

// PHP 8.5: the error will show the full path:
// executeTask() -> processData() -> nonExistentFunction()
//
// Before (PHP 8.4 and earlier):
// Fatal error: Call to undefined function nonExistentFunction() in /app/script.php on line 10
//
// Now (PHP 8.5+):
// Fatal error: Call to undefined function nonExistentFunction() in /app/script.php on line 10
// Stack trace:
// #0 /app/script.php(10): processData()
// #1 /app/script.php(15): executeTask()
// #2 /app/script.php(20): {main}
