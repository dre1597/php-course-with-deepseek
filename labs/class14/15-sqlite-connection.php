<?php
class SQLiteConnection {
    private static ?PDO $instance = null;

    public static function get(string $dbPath = null): PDO {
        if (self::$instance === null) {
            $path = $dbPath ?? __DIR__ . '/database.sqlite';
            self::$instance = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Habilita foreign keys no SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instance;
    }

    public static function inicializar(): void {
        $pdo = self::get();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                name      TEXT NOT NULL,
                email     TEXT NOT NULL UNIQUE,
                password     TEXT NOT NULL,
                created_at TEXT DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }
}

// Uso
$pdo = SQLiteConnection::get();
SQLiteConnection::inicializar();
