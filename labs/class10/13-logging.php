<?php

// === 13 — Basic Logging with error_log() ===

// Mode 0: default system log
error_log('System started successfully');

// Mode 3: write to specific file
error_log('Error connecting to database' . PHP_EOL, 3, __DIR__ . '/logs/database.log');

// Mode 1: send via email (use with caution in production!)
// error_log('Critical error detected', 1, 'admin@example.com');

// === Simple Logger for Applications ===

class Logger
{
    public function __construct(
        private string $file,
    ) {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s.v');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        error_log($logLine, 3, $this->file);
    }
}

// Usage:
$logger = new Logger(__DIR__ . '/logs/app.log');

$logger->info('User authenticated', ['user_id' => 42]);
$logger->warning('Login attempt failed', ['ip' => '192.168.1.1']);
$logger->error('Payment processing failed', ['order_id' => 789]);
$logger->debug('Query executed', ['sql' => 'SELECT ...', 'time_ms' => 12.5]);

// Log content:
// [2026-08-04 10:30:00.123] [INFO] User authenticated {"user_id":42}
// [2026-08-04 10:30:05.456] [WARNING] Login attempt failed {"ip":"192.168.1.1"}
// [2026-08-04 10:30:10.789] [ERROR] Payment processing failed {"order_id":789}
// [2026-08-04 10:30:15.012] [DEBUG] Query executed {"sql":"SELECT ...","time_ms":12.5}
