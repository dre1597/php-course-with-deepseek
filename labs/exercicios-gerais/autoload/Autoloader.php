<?php

class Autoloader
{
    private static array $prefixes = [];

    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        self::$prefixes[$prefix] = $baseDir;
    }

    public static function register(): void
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    public static function loadClass(string $class): void
    {
        $prefix = self::findMatchingPrefix($class);

        if ($prefix === null) {
            return;
        }

        $baseDir = self::$prefixes[$prefix];
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }

    private static function findMatchingPrefix(string $class): ?string
    {
        $class = $class . '\\';

        return array_find_key(self::$prefixes, fn($baseDir, $prefix) => str_starts_with($class, $prefix));
    }
}
