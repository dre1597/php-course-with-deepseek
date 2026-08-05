# Módulo 15: Segurança em PHP

## Visão Geral

Segurança não é opcional — é parte fundamental de qualquer aplicação web. Neste módulo, cobrimos os principais riscos (OWASP Top 10) e como mitigá-los com PHP puro, sem depender de frameworks.

---

## 1. XSS (Cross-Site Scripting)

XSS ocorre quando um atacante injeta JavaScript malicioso que roda no navegador da vítima.

### ❌ Vulnerável

```php
<?php
// NUNCA faça isso — o HTML do usuário é renderizado como código
$name = $_GET['name'] ?? 'Visitante';
echo "Olá, {$name}!";
// URL: pagina.php?name=<script>alert('hackeado')</script>
// Resultado: o script é execut
```

### ✅ Protegido com `htmlspecialchars()`

```php
<?php
// SEMPRE escape no output
$name = $_GET['name'] ?? 'Visitante';
echo "Olá, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";
// Resultado no HTML: Olá, &lt;script&gt;alert('hackeado')&lt;/script&gt;!
// O script é exibido como texto, não execut
```

### Função Auxiliar para Output Seguro

```php
<?php
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Uso em templates PHP puro
?>
<p>Nome: <?= h($user['name']) ?></p>
<p>Email: <?= h($user['email']) ?></p>
<p>Bio: <?= nl2br(h($user['bio'])) ?></p>
<?php

// Em atributos HTML também:
?>
<input type="text" value="<?= h($_GET['q'] ?? '') ?>">
<a href="/perfil?id=<?= h($user['id']) ?>">Perfil
```

### Contextos Diferentes Exigem Escape Diferente

```php
<?php
// Contexto HTML
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Contexto JavaScript (dentro de <script>)
$encodedData = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "<script>var name = {$encodedData};</script>";

// Contexto URL (parâmetros)
echo urlencode($data);

// Contexto CSS
// Evite inserir dados de usuário em CSS. Se inevitável, sanitize forteme
```

> ⚠️ **Cuidado:** `htmlspecialchars()` escapa apenas no contexto HTML. Não protege dentro de `<script>`, `<style>` ou atributos como `onclick`. Cada contexto precisa do escape adequado.

---

## 2. SQL Injection

Já abordado no Módulo 14, mas vale reforçar:

```php
<?php
// ❌ VULNERÁVEL
$sql = "SELECT * FROM users WHERE email = '{$_POST['email']}'";

// ✅ PROTEGIDO — Prepared Statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute([':email' => $_POST['email']]);

// Prepared statement até para queries dinâmicas
$order = in_array($_GET['ordem'], ['name', 'email', 'id']) ? $_GET['ordem'] : 'id';
$direction = $_GET['direcao'] === 'desc' ? 'DESC' : 'ASC';
$sql = "SELECT * FROM users ORDER BY {$order} {$direction}";
$stmt = $pdo->prepare($sql);
$stmt->execut
```

---

## 3. Password Hashing: `password_hash()` e `password_verify()`

```php
<?php
// ==================== CADASTRO ====================
$plainPassword = $_POST['password'] ?? '';

// password_hash usa bcrypt por padrão (PHP 8.4+: custo padrão mudou de 10 para 12!)
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Hash gerado: {$hash}<br>\n";
// Exemplo: $2y$12$eMqBv... (bcrypt, custo 12)

// Opções personalizadas
$options = [
    'cost' => 12,            // PHP 8.4+: custo padrão subiu de 10 para 12
];
$customHash = password_hash($plainPassword, PASSWORD_BCRYPT, $options);

// Usando Argon2id (requer PHP compilado com suporte)
if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon = password_hash($plainPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64 MB
        'time_cost'   => 4,
        'threads'     => 3,
    ]);
    echo "Hash Argon2id: {$hashArgon}<br>\n";
}

// ==================== LOGIN ====================
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Senha correta!
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    session_regenerate_id(true);
    header('Location: /dashboard.php');
    exit;
} else {
    // Mensagem genérica — não revele se o email existe
    echo "Email ou password incorretos.";
}

// Verificar se o hash precisa ser re-gerado (algoritmo ou custo mudou)
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute([':password' => $newHash, ':id' => $user['id']]);
    echo "Hash atualizado.<br>\n
```

> **PHP 8.4+** — Custo padrão do bcrypt mudou de 10 para 12, oferecendo maior segurança contra ataques de força bruta. Hashes existentes continuam válidos, mas `password_needs_rehash()` pode ser usado para re-hash com o novo custo.

> **PHP 8.4+** — Suporte a password Argon2 via OpenSSL nos builds que usam a extensão OpenSSL.

### Validação de Força de Senha

```php
<?php
function validatePasswordStrength(string $password): array {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'A password deve ter no mínimo 8 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A password deve conter ao menos uma letra maiúscula.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A password deve conter ao menos uma letra minúscula.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'A password deve conter ao menos um número.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'A password deve conter ao menos um caractere especial.';
    }

    return $errors;
}

// Uso
$password = $_POST['password'] ?? '';
$passwordErrors = validatePasswordStrength($password);
if (!empty($passwordErrors)) {
    echo "<ul>\n";
    foreach ($passwordErrors as $error) {
        echo "<li>" . h($error) . "</li>\n";
    }
    echo "</ul>\n
```

---

## 4. CSRF (Cross-Site Request Forgery)

Já abordado no Módulo 12. Reforce sempre:

```php
<?php
// Geração de token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Campo hidden
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// Verificação no POST
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Requisição inválida.');
}

// Renovar token após uso (opcional, aumenta segurança)
$_SESSION['csrf_token'] = bin2hex(random_bytes(3
```

### CSRF + SameSite Cookies

```php
<?php
// Cookies SameSite já ajudam na proteção CSRF
setcookie('session', 'value', [
    'samesite' => 'Strict', // ou Lax
]);

// Strict: cookie nunca enviado em requisições cross-site
// Lax: cookie enviado apenas em GET cross-site (ex: clique em link)
// None: sempre enviado (requer Sec
```

---

## 5. File Upload Seguro

```php
<?php
function uploadSeguro(array $file, string $dir, array $options = []): array {
    $options = array_merge([
        'max_size'   => 5 * 1024 * 1024,
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'rename'     => true,
    ], $options);

    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload: código ' . $file['error'];
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > $options['max_size']) {
        $errors[] = 'Arquivo maior que o permitido (' . ($options['max_size'] / 1024 / 1024) . ' MB).';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($realMime, $options['mime_types'])) {
        $errors[] = "Tipo de arquivo '{$realMime}' não permitido.";
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $options['extensions'])) {
        $errors[] = "Extensão '{$extension}' não permitida.";
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

    return ['success' => false, 'errors' => ['Falha ao mover o arquivo.']
```

> ⚠️ **Cuidado:** Nunca use o nome original de arquivos de upload. Um atacante pode enviar `../../../etc/passwd` como nome. Sempre renomeie.

---

## 6. HTTPS, Cookies Seguros e HSTS

```php
<?php
// Forçar HTTPS em produção
if (($_SERVER['HTTPS'] ?? 'off') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') != 443) {
    // Em produção, redirecione para HTTPS
    if (getenv('APP_ENV') === 'production') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

// Cookies seguros
session_set_cookie_params([
    'secure'   => true,   // apenas HTTPS
    'httponly' => true,   // inacessível via JS
    'samesite' => 'Lax',
]);

// Header HSTS no PHP
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preloa
```

---

## 7. Headers de Segurança

```php
<?php
// Headers de segurança recomendados
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY'); // ou SAMEORIGIN
header('X-XSS-Protection: 0');    // obsoleto mas não custa

// Content Security Policy (CSP)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Desabilitar exposição da versão do PHP
// No php.ini: expose_php =
```

### Função para aplicar todos os headers

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

// Chamar no início de cada requisição
applySecurityHeader
```

---

## 8. Environment Variables para Credenciais

**Nunca hardcode credenciais no código.**

```php
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
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```

### `.gitignore` Essencial

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

## 9. Rate Limiting Básico

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
    echo "Email ou password incorretos.
```

---

## 10. Timing Attacks: `hash_equals()`

```php
<?php
// ❌ Comparação NÃO segura — vulnerável a timing attack
if ($receivedToken === $expectedToken) {
    // ...
}

// ✅ Comparação segura — tempo constante
if (hash_equals($expectedToken, $receivedToken)) {
    // ...
}

// Exemplo: verificação de token de API
function verifyAPIToken(string $receivedToken): bool {
    $actualToken = getenv('API_TOKEN');
    return hash_equals($actualToken, $receivedToken);
}

// Exemplo: verificação de CSRF
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('Token CSRF inválido.'
```

> 💡 **Dica:** `===` para de comparar no primeiro byte diferente, revelando (pelo tempo de resposta) quantos bytes do token estão corretos. `hash_equals()` compara todos os bytes de uma vez, em tempo constante.

---

## 11. PHP Ini Settings de Segurança

```ini
; php.ini de produção recomendado

; Não expor versão do PHP
expose_php = Off

; Desabilitar funções perigosas
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Desabilitar inclusão remota
allow_url_fopen = Off
allow_url_include = Off

; Cookies de sessão seguros
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = "Lax"
session.use_strict_mode = On
session.use_only_cookies = On
session.use_trans_sid = Off

; Limitar tamanho de upload e post
post_max_size = 8M
upload_max_filesize = 2M

; Desabilitar exibição de erros em produção
display_errors = Off
display_startup_errors = Off
log_errors = On

; Ocultar erros do usuário mas logar
error_reporting = E_ALL

; Open basedir — restringe acesso a arquivos
open_basedir = "/var/www/html:/tmp"

; Tempo máximo de execução
max_execution_time = 30
max_input_time = 60

; Limitar memória
memory_limit = 128M

```

---

## 12. OWASP Top 10 Simplificado

| # | Risco | Mitigação PHP |
|---|-------|---------------|
| **A01** | Broken Access Control | Verificar permissões em TODA requisição protegida |
| **A02** | Cryptographic Failures | `password_hash()`, HTTPS, cookies `secure` |
| **A03** | Injection | Prepared statements (PDO), nunca concatenar SQL |
| **A04** | Insecure Design | Rate limiting, validação server-side sempre |
| **A05** | Security Misconfiguration | php.ini correto, `expose_php=Off`, desabilitar `display_errors` |
| **A06** | Vulnerable Components | Manter PHP e bibliotecas atualizados |
| **A07** | Authentication Failures | `password_hash()`, `session_regenerate_id()`, `hash_equals()` |
| **A08** | Software/Data Integrity | Verificar assinaturas de bibliotecas, desabilitar `allow_url_include` |
| **A09** | Logging & Monitoring | `error_log()`, `log_errors=On`, monitorar falhas de login |
| **A10** | SSRF | Validar e restringir URLs em `file_get_contents()` e cURL |

---

## 13. Checklist de Segurança para Aplicações PHP

```php
<?php
// checklist-seguranca.php — Rode em desenvolvimento para auditar sua aplicação

class SecurityAuditor {
    private array $warnings = [];

    public function audit(): array {
        // PHP Version
        if (version_compare(PHP_VERSION, '8.2', '<')) {
            $this->warnings[] = "PHP " . PHP_VERSION . " está desatualizado. Use 8.2+.";
        }

        // expose_php
        if (ini_get('expose_php')) {
            $this->warnings[] = "'expose_php' está ligado. Desabilite em produção.";
        }

        // display_errors
        if (ini_get('display_errors')) {
            $this->warnings[] = "'display_errors' está ligado. Desabilite em produção.";
        }

        // Session security
        if (!ini_get('session.cookie_httponly')) {
            $this->warnings[] = "'session.cookie_httponly' desabilitado.";
        }

        if (!ini_get('session.use_strict_mode')) {
            $this->warnings[] = "'session.use_strict_mode' desabilitado.";
        }

        // allow_url_include
        if (ini_get('allow_url_include')) {
            $this->warnings[] = "'allow_url_include' está ligado. DESABILITE IMEDIATAMENTE!";
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
    echo "Nenhum problema crítico encontrado.<br>\n";
} else {
    echo "<h3>Problemas de segurança detectados:</h3>\n<ul>\n";
    foreach ($results as $warning) {
        echo "<li>" . h($warning) . "</li>\n";
    }
    echo "</ul>\n
```

---

## 14. Exemplo Prático: Funções Utilitárias de Segurança

```php
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
   
```

---

## 📚 Referências

- [OWASP Top 10 (2021)](https://owasp.org/www-project-top-ten/)
- [PHP: Segurança](https://www.php.net/manual/pt_BR/security.php)
- [PHP: password_hash](https://www.php.net/manual/pt_BR/function.password-hash.php)
- [PHP: password_verify](https://www.php.net/manual/pt_BR/function.password-verify.php)
- [PHP: hash_equals](https://www.php.net/manual/pt_BR/function.hash-equals.php)
- [PHP: htmlspecialchars](https://www.php.net/manual/pt_BR/function.htmlspecialchars.php)
- [Paragonie: PHP Security Guide](https://paragonie.com/blog/2017/12/2018-guide-building-secure-php-software)
- [OWASP: Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [MDN: Content Security Policy (CSP)](https://developer.mozilla.org/pt-BR/docs/Web/HTTP/CSP)
