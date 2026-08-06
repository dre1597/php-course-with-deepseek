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
