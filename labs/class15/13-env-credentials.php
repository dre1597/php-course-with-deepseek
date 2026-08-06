<?php
// ❌ NUNCA FAÇA ISSO
$dbPassword = 'supersenha123';
$apiKey  = 'sk-abc123xyz';

// ✅ Use variáveis de ambiente
$dbPassword = getenv('DB_PASSWORD');
$apiKey  = getenv('API_KEY');

// Ou via $_ENV (se variables_order incluir E)
$dbPassword = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

// Ou via arquivo .env com biblioteca vlucas/phpdotenv
// composer require vlucas/phpdotenv

// Configuração centralizada
class Config {
    public static function get(string $key, mixed $default = null): mixed {
        return getenv($key) ?: $default;
    }

    public static function dbHost(): string  { return self::get('DB_HOST', 'localhost'); }
    public static function dbName(): string  { return self::get('DB_NAME', 'app'); }
    public static function dbUser(): string  { return self::get('DB_USER', 'root'); }
    public static function dbPass(): string  { return self::get('DB_PASS', ''); }
    public static function appEnv(): string  { return self::get('APP_ENV', 'production'); }
    public static function appDebug(): bool  { return self::get('APP_DEBUG', 'false') === 'true'; }
}

$pdo = new PDO(
    "mysql:host=" . Config::dbHost() . ";dbname=" . Config::dbName() . ";charset=utf8mb4",
    Config::dbUser(),
    Config::dbPass(),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
