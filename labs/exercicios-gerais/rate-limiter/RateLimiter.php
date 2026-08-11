<?php

class RateLimiter
{
    private const int SESSION_LIMIT = 5;
    private const int IP_LIMIT = 20;
    private const int WINDOW_SECONDS = 900;
    private const string COOKIE_NAME = '__rl_sid';

    private string $sessionId;
    private string $ip;
    private string $storageDir;

    public function __construct(
        ?string $storageDir = null,
        ?string $sessionId = null,
        ?string $ip = null
    )
    {
        $this->storageDir = $storageDir ?? sys_get_temp_dir() . '/rate-limiter';
        $this->ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $this->sessionId = $sessionId ?? $this->resolveSessionId();

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function attempt(): bool
    {
        $sessionData = $this->load('session_' . $this->sessionId);
        $ipData = $this->load('ip_' . md5($this->ip));

        $now = time();

        if ($this->isBlocked($sessionData, $now) || $this->isBlocked($ipData, $now)) {
            $this->send429Header($sessionData, $ipData, $now);
            return false;
        }

        $this->prune($sessionData, $now);
        $this->prune($ipData, $now);

        $sessionData['attempts'][] = $now;
        $ipData['attempts'][] = $now;

        if (count($sessionData['attempts']) >= self::SESSION_LIMIT) {
            $sessionData['blocked_until'] = $now + self::WINDOW_SECONDS;
        }

        if (count($ipData['attempts']) >= self::IP_LIMIT) {
            $ipData['blocked_until'] = $now + self::WINDOW_SECONDS;
        }

        $this->save('session_' . $this->sessionId, $sessionData);
        $this->save('ip_' . md5($this->ip), $ipData);

        return true;
    }

    public function getRemaining(): array
    {
        $sessionData = $this->load('session_' . $this->sessionId);
        $ipData = $this->load('ip_' . md5($this->ip));

        $now = time();
        $this->prune($sessionData, $now);
        $this->prune($ipData, $now);

        return [
            'session' => max(0, self::SESSION_LIMIT - count($sessionData['attempts'])),
            'ip' => max(0, self::IP_LIMIT - count($ipData['attempts'])),
        ];
    }

    public function getRetryAfter(): int
    {
        $sessionData = $this->load('session_' . $this->sessionId);
        $ipData = $this->load('ip_' . md5($this->ip));

        $now = time();

        $retry = 0;
        if (($sessionData['blocked_until'] ?? 0) > $now) {
            $retry = $sessionData['blocked_until'] - $now;
        }
        if (($ipData['blocked_until'] ?? 0) > $now) {
            $blocked = $ipData['blocked_until'] - $now;
            $retry = max($retry, $blocked);
        }

        return max(0, $retry);
    }

    public function reset(): void
    {
        $sessionFile = $this->filePath('session_' . $this->sessionId);
        $ipFile = $this->filePath('ip_' . md5($this->ip));

        @unlink($sessionFile);
        @unlink($ipFile);
    }

    private function isBlocked(array $data, int $now): bool
    {
        return ($data['blocked_until'] ?? 0) > $now;
    }

    private function prune(array &$data, int $now): void
    {
        $data['attempts'] = array_values(array_filter(
            $data['attempts'] ?? [],
            fn(int $ts) => $ts > $now - self::WINDOW_SECONDS
        ));

        if (($data['blocked_until'] ?? 0) <= $now) {
            $data['blocked_until'] = null;
        }
    }

    private function load(string $key): array
    {
        $file = $this->filePath($key);

        if (!file_exists($file)) {
            return ['attempts' => [], 'blocked_until' => null];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        return is_array($data) ? $data : ['attempts' => [], 'blocked_until' => null];
    }

    private function save(string $key, array $data): void
    {
        $file = $this->filePath($key);
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    private function filePath(string $key): string
    {
        return $this->storageDir . '/' . $key . '.json';
    }

    private function resolveSessionId(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE && session_id() !== '') {
            return session_id();
        }

        if (isset($_COOKIE[self::COOKIE_NAME])) {
            return $_COOKIE[self::COOKIE_NAME];
        }

        $id = bin2hex(random_bytes(16));
        setcookie(self::COOKIE_NAME, $id, [
            'expires' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        $_COOKIE[self::COOKIE_NAME] = $id;

        return $id;
    }

    private function send429Header(array $sessionData, array $ipData, int $now): void
    {
        $retry = 0;
        if (($sessionData['blocked_until'] ?? 0) > $now) {
            $retry = $sessionData['blocked_until'] - $now;
        }
        if (($ipData['blocked_until'] ?? 0) > $now) {
            $retry = max($retry, $ipData['blocked_until'] - $now);
        }

        header('HTTP/1.1 429 Too Many Requests');
        header('Retry-After: ' . max(0, $retry));
    }
}
