<?php
class RateLimiter {
    private string $cacheDir;

    public function __construct(string $cacheDir = null) {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir();
    }

    public function attempt(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool {
        $file = $this->cacheDir . '/rate_' . md5($key) . '.json';
        $now = time();

        $data = [];
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?? [];
        }

        // Remove tentativas fora da janela
        $data = array_filter($data, fn($timestamp) => $timestamp > ($now - $windowSeconds));

        // Verifica limite
        if (count($data) >= $maxAttempts) {
            return false;
        }

        // Registra tentativa
        $data[] = $now;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return true;
    }

    public function remainingTime(string $key, int $windowSeconds = 300): int {
        $file = $this->cacheDir . '/rate_' . md5($key) . '.json';
        if (!file_exists($file)) {
            return 0;
        }
        $data = json_decode(file_get_contents($file), true) ?? [];
        if (empty($data)) {
            return 0;
        }
        $oldest = min($data);
        return max(0, $oldest + $windowSeconds - time());
    }
}

// Uso: proteger endpoint de login
$ip = $_SERVER['REMOTE_ADDR'];
$limiter = new RateLimiter();

    if (!$limiter->attempt("login:{$ip}", maxAttempts: 5, windowSeconds: 300)) {
    $remaining = $limiter->remainingTime("login:{$ip}", 300);
    http_response_code(429);
    die("Muitas tentativas. Aguarde {$remaining} segundos.");
}

// Processa login...
if ($loginFailed) {
    echo "Email ou password incorretos.";
}
