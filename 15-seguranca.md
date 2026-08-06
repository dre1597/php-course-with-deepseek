# Module 15: PHP Security

## Overview

Security is not optional — it's a fundamental part of any web application. This module covers the main risks (OWASP Top 10) and how to mitigate them with plain PHP.

---

## 1. XSS (Cross-Site Scripting)

XSS happens when an attacker injects malicious JavaScript that runs in the victim's browser.

### ❌ Vulnerable

```php
<?php
// NEVER do this — user HTML is rendered as code
$name = $_GET['name'] ?? 'Visitor';
echo "Hello, {$name}!";
// URL: page.php?name=<script>alert('hacked')</script>
// Result: the script executes as JavaScript!
```

### ✅ Protected with `htmlspecialchars()`

```php
<?php
// ALWAYS escape on output
$name = $_GET['name'] ?? 'Visitor';
echo "Hello, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";
// HTML result: Hello, &lt;script&gt;alert('hacked')&lt;/script&gt;!
// The script is displayed as text, not executed
```

### Helper Function for Safe Output

```php
<?php
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Usage in pure PHP templates
?>
<p>Name: <?= h($user['name']) ?></p>
<p>Email: <?= h($user['email']) ?></p>
<p>Bio: <?= nl2br(h($user['bio'])) ?></p>
<?php

// In HTML attributes too:
?>
<input type="text" value="<?= h($_GET['q'] ?? '') ?>">
<a href="/profile?id=<?= h($user['id']) ?>">Profile</a>
```

The flags ENT_QUOTES | ENT_HTML5 are important: ENT_QUOTES escapes both single and double quotes (without it, `'` is left unescaped — dangerous inside `onclick='...'`). ENT_HTML5 uses the HTML5 entity set instead of the older XHTML/HTML 4 one.

### Different Contexts Require Different Escaping

```php
<?php
// HTML context
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// JavaScript context (inside <script>)
$encodedData = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "<script>var name = {$encodedData};</script>";

// URL context (parameters)
echo urlencode($data);

// CSS context
// Avoid user data in CSS. If unavoidable, sanitize strictly.
```

> **Warning:** `htmlspecialchars()` only escapes in HTML context. It does not protect inside `<script>`, `<style>`, or attributes like `onclick`. Each context needs the proper escaping.

---

## 2. SQL Injection

Already covered in Module 14, but worth reinforcing:

```php
<?php
// ❌ VULNERABLE
$sql = "SELECT * FROM users WHERE email = '{$_POST['email']}'";

// ✅ PROTECTED — Prepared Statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute([':email' => $_POST['email']]);

// Prepared statements even for dynamic queries
$order = in_array($_GET['order'], ['name', 'email', 'id']) ? $_GET['order'] : 'id';
$direction = $_GET['direction'] === 'desc' ? 'DESC' : 'ASC';
$sql = "SELECT * FROM users ORDER BY {$order} {$direction}";
$stmt = $pdo->prepare($sql);
$stmt->execute();
```

---

## 3. Password Hashing: `password_hash()` and `password_verify()`

```php
<?php
// ==================== REGISTRATION ====================
$plainPassword = $_POST['password'] ?? '';

// password_hash uses bcrypt by default (PHP 8.4+: default cost changed from 10 to 12!)
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Generated hash: {$hash}<br>\n";
// Example: $2y$12$eMqBv... (bcrypt, cost 12)

// Custom options
$options = [
    'cost' => 12,            // PHP 8.4+: default cost increased from 10 to 12
];
$customHash = password_hash($plainPassword, PASSWORD_BCRYPT, $options);

// Using Argon2id (requires PHP compiled with support)
if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon = password_hash($plainPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64 MB
        'time_cost'   => 4,
        'threads'     => 3,
    ]);
    echo "Argon2id hash: {$hashArgon}<br>\n";
}

// ==================== LOGIN ====================
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Password correct!
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    session_regenerate_id(true);
    header('Location: /dashboard.php');
    exit;
} else {
    // Generic message — don't reveal if the email exists
    echo "Incorrect email or password.";
}

// Check if hash needs to be re-generated (algorithm or cost changed)
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute([':password' => $newHash, ':id' => $user['id']]);
    echo "Hash updated.<br>\n";
}
```

> **PHP 8.4+** — Default bcrypt cost changed from 10 to 12, providing greater security against brute force attacks. Existing hashes remain valid, but `password_needs_rehash()` can be used to re-hash with the new cost.

> **PHP 8.4+** — Argon2 password support via OpenSSL in builds that use the OpenSSL extension.

### Password Strength Validation

```php
<?php
function validatePasswordStrength(string $password): array {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    return $errors;
}

// Usage
$password = $_POST['password'] ?? '';
$passwordErrors = validatePasswordStrength($password);
if (!empty($passwordErrors)) {
    echo "<ul>\n";
    foreach ($passwordErrors as $error) {
        echo "<li>" . h($error) . "</li>\n";
    }
    echo "</ul>\n";
}
```

---

## 4. CSRF (Cross-Site Request Forgery)

Already covered in Module 12. Always reinforce:

```php
<?php
// Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Hidden field
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// POST verification
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Invalid request.');
}

// Renew token after use (optional, increases security)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

### CSRF + SameSite Cookies

```php
<?php
// SameSite cookies already help with CSRF protection
setcookie('session', 'value', [
    'samesite' => 'Strict', // or Lax
]);

// Strict: cookie never sent in cross-site requests
// Lax: cookie sent only in GET cross-site (e.g., link click)
// None: always sent (requires Secure attribute)
```

---

## 5. Secure File Upload

```php
<?php
function secureUpload(array $file, string $dir, array $options = []): array {
    $options = array_merge([
        'max_size'   => 5 * 1024 * 1024,
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'rename'     => true,
    ], $options);

    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload error: code ' . $file['error'];
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > $options['max_size']) {
        $errors[] = 'File larger than allowed (' . ($options['max_size'] / 1024 / 1024) . ' MB).';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($realMime, $options['mime_types'])) {
        $errors[] = "File type '{$realMime}' not allowed.";
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $options['extensions'])) {
        $errors[] = "Extension '{$extension}' not allowed.";
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if ($options['rename']) {
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    } else {
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $fileName = $safeName;
    }

    $destination = rtrim($dir, '/') . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success'       => true,
            'original_name' => $file['name'],
            'final_name'    => $fileName,
            'path'          => $destination,
            'size'          => $file['size'],
            'type'          => $realMime,
        ];
    }

    return ['success' => false, 'errors' => ['Failed to move file.']];
}
```

> **Warning:** Never use the original filename of uploads. An attacker can send `../../../etc/passwd` as the name. Always rename.

---

## 6. HTTPS, Secure Cookies, and HSTS

```php
<?php
// Force HTTPS in production
if (($_SERVER['HTTPS'] ?? 'off') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') != 443) {
    // In production, redirect to HTTPS
    if (getenv('APP_ENV') === 'production') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

// Secure cookies
session_set_cookie_params([
    'secure'   => true,   // HTTPS only
    'httponly' => true,   // inaccessible via JS
    'samesite' => 'Lax',
]);

// HSTS header in PHP
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
```

---

## 7. Security Headers

```php
<?php
// Recommended security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY'); // or SAMEORIGIN
header('X-XSS-Protection: 0');    // deprecated but harmless

// Content Security Policy (CSP)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Disable PHP version exposure
// In php.ini: expose_php = Off
```

### Function to Apply All Headers

```php
<?php
function applySecurityHeaders(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (!headers_sent()) {
        header('Content-Security-Policy: '
            . "default-src 'self'; "
            . "script-src 'self'; "
            . "style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' data: https:; "
            . "font-src 'self'; "
            . "connect-src 'self'; "
            . "frame-ancestors 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self';"
        );
    }
}

// Call at the start of each request
applySecurityHeaders();
```

---

## 8. Environment Variables for Credentials

**Never hardcode credentials in your code.**

```php
<?php
// ❌ NEVER DO THIS
$dbPassword = 'supersecret123';
$apiKey  = 'sk-abc123xyz';

// ✅ Use environment variables
$dbPassword = getenv('DB_PASSWORD');
$apiKey  = getenv('API_KEY');

// Or via $_ENV (if variables_order includes E)
$dbPassword = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

// Or via .env file with vlucas/phpdotenv library
// composer require vlucas/phpdotenv

// Centralized configuration
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
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
```

### Essential `.gitignore`

```gitignore
# .gitignore
.env
.env.local
.env.*.local
vendor/
node_modules/
*.log
/backups/
/uploads/*
!/uploads/.gitkeep
.DS_Store
Thumbs.db

```

---

## 9. Basic Rate Limiting

```php
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

        // Remove attempts outside the window
        $data = array_filter($data, fn($timestamp) => $timestamp > ($now - $windowSeconds));

        // Check limit
        if (count($data) >= $maxAttempts) {
            return false;
        }

        // Record attempt
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

// Usage: protect login endpoint
$ip = $_SERVER['REMOTE_ADDR'];
$limiter = new RateLimiter();

if (!$limiter->attempt("login:{$ip}", maxAttempts: 5, windowSeconds: 300)) {
    $remaining = $limiter->remainingTime("login:{$ip}", 300);
    http_response_code(429);
    die("Too many attempts. Wait {$remaining} seconds.");
}

// Process login...
if ($loginFailed) {
    echo "Incorrect email or password.";
}
```

---

## 10. Timing Attacks: `hash_equals()`

```php
<?php
// ❌ UNSAFE comparison — vulnerable to timing attack
if ($receivedToken === $expectedToken) {
    // ...
}

// ✅ Safe comparison — constant time
if (hash_equals($expectedToken, $receivedToken)) {
    // ...
}

// Example: API token verification
function verifyAPIToken(string $receivedToken): bool {
    $actualToken = getenv('API_TOKEN');
    return hash_equals($actualToken, $receivedToken);
}

// Example: CSRF verification
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token.');
}
```

> **Tip:** `===` stops comparing at the first different byte, revealing (by response time) how many bytes of the token are correct. `hash_equals()` compares all bytes at once, in constant time.

---

## 11. PHP Ini Security Settings

```ini
; Recommended production php.ini

; Don't expose PHP version
expose_php = Off

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Disable remote inclusion
allow_url_fopen = Off
allow_url_include = Off

; Secure session cookies
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = "Lax"
session.use_strict_mode = On
session.use_only_cookies = On
session.use_trans_sid = Off

; Limit upload and post size
post_max_size = 8M
upload_max_filesize = 2M

; Disable error display in production
display_errors = Off
display_startup_errors = Off
log_errors = On

; Hide errors from user but log them
error_reporting = E_ALL

; Open basedir — restrict file access
open_basedir = "/var/www/html:/tmp"

; Maximum execution time
max_execution_time = 30
max_input_time = 60

; Limit memory
memory_limit = 128M

```

---

## 12. OWASP Top 10 — Simplified

| # | Risk | PHP Mitigation |
|---|-------|---------------|
| A01 | Broken Access Control | if (!$user->can('edit')) { http_response_code(403); exit; } on every protected page |
| A02 | Cryptographic Failures | password_hash(), HTTPS, session.cookie_secure=On |
| A03 | Injection | $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id'); NEVER concatenate user input into SQL |
| A04 | Insecure Design | Rate limiting on login, validate input server-side even if client already validates |
| A05 | Security Misconfiguration | expose_php=Off, display_errors=Off in production, remove default credentials |
| A06 | Vulnerable Components | Keep PHP updated (8.2+), run composer audit, check CVE databases |
| A07 | Authentication Failures | password_hash() + password_verify(), session_regenerate_id(true) after login |
| A08 | Software/Data Integrity | Disable allow_url_include, verify checksums, lock composer.lock |
| A09 | Logging & Monitoring | error_log($context), log_errors=On, monitor failed logins, never log passwords |
| A10 | SSRF | Whitelist allowed URLs, use parse_url() + validate host before file_get_contents() with user input |

---

## 13. Security Checklist for PHP Applications

```php
<?php
// security-checklist.php — Run in development to audit your application

class SecurityAuditor {
    private array $warnings = [];

    public function audit(): array {
        // PHP Version
        if (version_compare(PHP_VERSION, '8.2', '<')) {
            $this->warnings[] = "PHP " . PHP_VERSION . " is outdated. Use 8.2+.";
        }

        // expose_php
        if (ini_get('expose_php')) {
            $this->warnings[] = "'expose_php' is on. Disable in production.";
        }

        // display_errors
        if (ini_get('display_errors')) {
            $this->warnings[] = "'display_errors' is on. Disable in production.";
        }

        // Session security
        if (!ini_get('session.cookie_httponly')) {
            $this->warnings[] = "'session.cookie_httponly' disabled.";
        }

        if (!ini_get('session.use_strict_mode')) {
            $this->warnings[] = "'session.use_strict_mode' disabled.";
        }

        // allow_url_include
        if (ini_get('allow_url_include')) {
            $this->warnings[] = "'allow_url_include' is on. DISABLE IMMEDIATELY!";
        }

        return $this->warnings;
    }

    public function getWarnings(): array {
        return $this->warnings;
    }
}

$auditor = new SecurityAuditor();
$results = $auditor->audit();

if (empty($results)) {
    echo "No critical issues found.<br>\n";
} else {
    echo "<h3>Security issues detected:</h3>\n<ul>\n";
    foreach ($results as $warning) {
        echo "<li>" . h($warning) . "</li>\n";
    }
    echo "</ul>\n";
}
```

---

## 14. Practical Example: Security Utility Functions

```php
<?php
// security.php — Include this file in your application bootstrap

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
        die('Invalid request.');
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
```

---

## Navigation

- [← Module 14: Database with PDO](./14-banco-de-dados.md)
- [Module 16: Automated Testing with PHPUnit →](./16-testes-automatizados.md)

## References

- [OWASP Top 10 (2021)](https://owasp.org/www-project-top-ten/)
- [PHP: Security](https://www.php.net/manual/en/security.php)
- [PHP: password_hash](https://www.php.net/manual/en/function.password-hash.php)
- [PHP: password_verify](https://www.php.net/manual/en/function.password-verify.php)
- [PHP: hash_equals](https://www.php.net/manual/en/function.hash-equals.php)
- [PHP: htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php)
- [Paragonie: PHP Security Guide](https://paragonie.com/blog/2017/12/2018-guide-building-secure-php-software)
- [OWASP: Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [MDN: Content Security Policy (CSP)](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
