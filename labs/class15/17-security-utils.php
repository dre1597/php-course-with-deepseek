<?php
// security.php — Inclua este arquivo no bootstrap da sua aplicação

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Requisição inválida.');
    }
}

function sanitizeInput(string $data): string {
    return trim(strip_tags($data));
}

function validateEmail(string $email): string|false {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function generateRandomToken(int $bytes = 32): string {
    return bin2hex(random_bytes($bytes));
}

function secureRedirect(string $url): never {
    $url = filter_var($url, FILTER_VALIDATE_URL)
        ?? filter_var($url, FILTER_SANITIZE_URL);

    header("Location: {$url}");
    exit;
}

function clientIp(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

function requireHTTPS(): void {
    if (
        empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
    ) {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $url, true, 301);
        exit;
    }
}
