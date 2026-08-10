<?php

class RotatingLogger
{
    private string $logDir;
    private string $logName;
    private int $maxSize;
    private int $maxFiles;

    private const int DEFAULT_MAX_SIZE = 1_048_576;
    private const int DEFAULT_MAX_FILES = 5;

    public function __construct(string $logPath, int $maxSize = self::DEFAULT_MAX_SIZE, int $maxFiles = self::DEFAULT_MAX_FILES)
    {
        $this->logDir = dirname($logPath);
        $this->logName = basename($logPath);
        $this->maxSize = $maxSize;
        $this->maxFiles = $maxFiles;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        $entry = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, $message);

        $path = $this->logDir . '/' . $this->logName;
        file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);

        clearstatcache(true, $path);

        if (filesize($path) >= $this->maxSize) {
            $this->rotate();
        }
    }

    private function rotate(): void
    {
        $path = $this->logDir . '/' . $this->logName;
        $rotatedName = $this->logName . '.' . date('Y-m-d_His') . '_' . uniqid();

        rename($path, $this->logDir . '/' . $rotatedName);
        $this->cleanup();
    }

    private function cleanup(): void
    {
        $files = glob($this->logDir . '/' . $this->logName . '.*');

        if ($files === false || count($files) <= $this->maxFiles) {
            return;
        }

        sort($files);
        $excess = count($files) - $this->maxFiles;

        for ($i = 0; $i < $excess; $i++) {
            unlink($files[$i]);
        }
    }
}
