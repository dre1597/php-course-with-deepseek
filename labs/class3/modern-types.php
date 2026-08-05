<?php

// mixed

function findById(int $id): ?string
{
    // ?string is equivalent to string|null
    if ($id === 0) {
        return null;
    }
    return "Record #{$id}";
}

$result = findById(0);
var_dump($result);

$result = findById(42);
var_dump($result);

// void

function logMessage(string $msg): void
{
    error_log($msg);
    return;
}

// never

function redirect(string $url): never
{
    header("Location: {$url}");
    exit();
}

function fatalError(string $message): never
{
    throw new \RuntimeException($message);
}

function invalidType(): never
{
    // PHP entende que nunca chega depois disso
}

// false as standalone type (PHP 8.1+)

// Useful for functions that return false as failure indicator
function strpos_fake(string $haystack, string $needle): int|false
{
    $pos = strpos($haystack, $needle);
    return $pos; // int ou false
}

// null como tipo standalone (PHP 8.2+)
function getConfig(string $key): string|null
{
    // string|null instead of ?string (equivalent)
    $config = ['app' => 'MyApp'];
    return $config[$key] ?? null;
}

// true

// PHP 8.2+ allows true as a type (useful in union types)
interface Validatable
{
    public function validate(): true|string;
    // Returns true if valid, or error message string
}

class Email implements Validatable
{
    public function __construct(private string $value)
    {
    }

    public function validate(): true|string
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return "Email '{$this->value}' is invalid";
        }
        return true;
    }
}