<?php

// === 12 — #[\Deprecated] Attribute (PHP 8.4+) ===

class LegacyLibrary
{
    #[\Deprecated(
        message: 'Use getUserById() instead',
        since: '2.0.0',
    )]
    public function findUser(int $id): ?array
    {
        // old implementation
        return null;
    }

    public function getUserById(int $id): ?array
    {
        // new implementation
        return null;
    }
}

class Formatter
{
    #[\Deprecated(
        message: 'Use Format::format() instead',
        since: '3.5',
    )]
    public const OLD_FORMAT = 'Y-m-d';

    public const NEW_FORMAT = 'd/m/Y';
}

$lib = new LegacyLibrary();

// Calling deprecated method emits E_USER_DEPRECATED:
// $lib->findUser(1);
// Deprecated: LegacyLibrary::findUser() is deprecated,
// Use getUserById() instead

// === Migration Example with Error Handler ===

// In development, convert deprecated to exception
set_error_handler(function (int $severity, string $msg): bool {
    if ($severity === E_USER_DEPRECATED || $severity === E_DEPRECATED) {
        throw new ErrorException($msg, 0, $severity);
    }
    return false;
});

class Service
{
    #[\Deprecated(message: 'Use NewService', since: '3.0')]
    public function execute(): string
    {
        return 'OK (old)';
    }
}

try {
    $s = new Service();
    $s->execute(); // Throws ErrorException in dev — you won't forget to migrate!
} catch (ErrorException $e) {
    echo "Pending migration: " . $e->getMessage() . PHP_EOL;
}
