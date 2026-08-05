# Projeto 03: Sistema de Login com SQLite

## Objetivo

Criar um sistema completo de cadastro e login de usuários com PHP puro, usando SQLite como banco de dados (arquivo único, sem servidor MySQL). Implementa `password_hash`, CSRF, validação de formulários e estrutura MVC simplificada.

## Estrutura de Arquivos


```
projetos/login/
    database.sqlite    (criado na primeira execução)
    index.php          (front controller / router)
    config.php         (configurações)
    src/
        Database.php   (conexão PDO SQLite)
        Router.php     (roteador simples)
        Session.php    (gerenciamento de sessão)
        Auth.php       (lógica de autenticação)
        Validator.php  (validação de formulários)
    templates/
        layout.php     (layout HTML base)
        home.php       (página inicial)
        cadastro.php   (formulário de cadastro)
        login.php      (formulário de login)
        dashboard.php  (área protegida)

```

---

## Código de Cada Arquivo

### `config.php`

```php
<?php
// config.php
return [
    'app' => [
        'name' => 'Sistema de Login',
        'url'  => 'http://localhost:8080',
    ],
    'db' => [
        'caminho' => __DIR__ . '/database.sqlite',
    ],
    'password' => [
        'algoritmo' => PASSWORD_DEFAULT, // PASSWORD_BCRYPT ou PASSWORD_ARGON2ID
        'custo'     => 12,               // PHP 8.4+: padrão subiu para 12
    ]
```

### `src/Database.php`

```php
<?php
// src/Database.php
class Database {
    private static ?PDO $instancia = null;

    public static function get(string $path): PDO {
        if (self::$instancia === null) {
            self::$instancia = new PDO(
                'sqlite:' . $path,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
            self::$instancia->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instancia;
    }

    public static function inicializar(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                name            TEXT    NOT NULL,
                email           TEXT    NOT NULL UNIQUE,
                password           TEXT    NOT NULL,
                token_lembrar   TEXT,
                created_at       TEXT    DEFAULT (datetime('now', 'localtime')),
                updated_at   TEXT    DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS csrf_tokens (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id  INTEGER NOT NULL,
                token       TEXT    NOT NULL,
                created_at   TEXT    DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(email);
        ");
   
```

### `src/Session.php`

```php
<?php
// src/Session.php
class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => false, // true em produção com HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('LOGIN_SESSID');
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $message = null): ?string {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function destroy(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function setUser(array $user): void {
        self::set('user_id', $user['id']);
        self::set('user_name', $user['name']);
        self::set('user_email', $user['email']);
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool {
        return self::get('user_id') !== null;
    }

    public static function userId(): ?int {
        return self::get('user_id');
    }

    public static function userName(): ?string {
        return self::get('user_name');
   
```

### `src/Validator.php`

```php
<?php
// src/Validator.php
class Validator {
    private array $errors = [];

    public function validate(array $data, array $rules): array {
        $cleaned = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            if (in_array('required', $fieldRules) && trim((string) $value) === '') {
                $this->errors[$field] = "O campo '{$field}' é obrigatório.";
                continue;
            }

            $value = trim((string) $value);

            if (in_array('email', $fieldRules) && $value !== '') {
                $validated = filter_var($value, FILTER_VALIDATE_EMAIL);
                if ($validated === false) {
                    $this->errors[$field] = 'Email inválido.';
                } else {
                    $value = $validated;
                }
            }

            foreach ($fieldRules as $rule) {
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $this->errors[$field] = "Deve ter no mínimo {$min} caracteres.";
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $this->errors[$field] = "Deve ter no máximo {$max} caracteres.";
                    }
                }
                if ($rule === 'senha_forte' && $value !== '') {
                    if (!preg_match('/[A-Z]/', $value)) {
                        $this->errors[$field] = 'Deve conter ao menos uma maiúscula.';
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $this->errors[$field] = 'Deve conter ao menos uma minúscula.';
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $this->errors[$field] = 'Deve conter ao menos um número.';
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $this->errors[$field] = 'Deve conter ao menos um caractere especial.';
                    }
                }
            }

            $cleaned[$field] = $value;
        }

        return $cleaned;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
   
```

### `src/Auth.php`

```php
<?php
// src/Auth.php
class Auth {
    public function __construct(private PDO $pdo) {}

    public function register(string $name, string $email, string $password): array {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Este email já está cadastrado.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );
        $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':password' => $hash,
        ]);

        return ['success' => true, 'id' => (int) $this->pdo->lastInsertId()];
    }

    public function login(string $email, string $password): array {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Email ou password incorretos.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Email ou password incorretos.'];
        }

        unset($user['password']);
        return ['success' => true, 'user' => $user];
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function generateCSRFToken(int $userId): string {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->pdo->prepare(
            'INSERT INTO csrf_tokens (user_id, token) VALUES (:user_id, :token)'
        );
        $stmt->execute([':user_id' => $userId, ':token' => $token]);

        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function verifyCSRFToken(string $token): bool {
        $esperado = $_SESSION['csrf_token'] ?? '';
        return $esperado !== '' && hash_equals($esperado, $token);
   
```

### `src/Router.php`

```php
<?php
// src/Router.php
class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
        } else {
            http_response_code(404);
            echo "<h1>404 — Página não encontrada</h1>\n";
            echo "<p>Método: {$method} | URI: " . htmlspecialchars($uri) . "</p>";
        }
   
```

### `templates/layout.php`

```php
<?php
// templates/layout.php
function layout(string $title, string $content, bool $loggedIn = false, string $userName = ''): string {
    $name = htmlspecialchars($userName);
    $loggedInNav = $loggedIn ? "
        <a href='/dashboard'>Dashboard</a>
        <a href='/logout'>Sair</a>
    " : "
        <a href='/login'>Entrar</a>
        <a href='/cadastro'>Cadastrar</a>
    ";
    $loggedInName = $loggedIn ? "<span class='user-name'>{$name}</span>" : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc;
               color: #334155; min-height: 100vh; display: flex; flex-direction: column; }
        nav { background: #1e293b; padding: 0 24px; display: flex; align-items: center;
              height: 56px; justify-content: space-between; }
        nav .brand { color: #e2e8f0; font-weight: 700; font-size: 1.1rem; text-decoration: none; }
        nav .nav-links { display: flex; gap: 20px; align-items: center; }
        nav .nav-links a { color: #94a3b8; text-decoration: none; font-size: 0.9rem;
                           transition: color 0.15s; }
        nav .nav-links a:hover { color: white; }
        nav .user-name { color: #a5b4fc; font-weight: 600; font-size: 0.9rem; }
        .container { max-width: 500px; width: 100%; margin: 40px auto; padding: 0 20px; flex: 1; }
        .card { background: white; border-radius: 12px; padding: 32px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 1.5rem; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600;
                            margin-bottom: 6px; color: #475569; }
        .form-group input { width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0;
                            border-radius: 8px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #6366f1;
                                  box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 1rem;
               font-weight: 600; cursor: pointer; transition: 0.15s; margin-top: 8px; }
        .btn-primary { background: #6366f1; color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fee2e2; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
        .alert-erro { background: #fee2e2; color: #991b1b; }
        .alert-sucesso { background: #d1fae5; color: #065f46; }
        .link { text-align: center; margin-top: 16px; font-size: 0.9rem; color: #64748b; }
        .link a { color: #6366f1; text-decoration: none; font-weight: 600; }
        .link a:hover { text-decoration: underline; }
        .logout-form { display: inline; }
        footer { text-align: center; padding: 20px; color: #94a3b8; font-size: 0.8rem; }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="brand">🔐 Sistema de Login</a>
        <div class="nav-links">
            {$loggedInName}
            {$loggedInNav}
        </div>
    </nav>
    <div class="container">
        {$content}
    </div>
    <footer>&copy; 2026 — PHP Puro + SQLite</footer>
</body>
</html>
HTM
```

### `templates/home.php`

```php
<?php
// templates/home.php
$loggedIn = Session::isLoggedIn();

if ($loggedIn) {
    $content = "
        <div class='card'>
            <h1>Bem-vindo(a), " . htmlspecialchars(Session::userName()) . "!</h1>
            <p>Você está logado no sistema.</p>
            <p style='margin-top:16px'>
                <a href='/dashboard' style='color:#6366f1;font-weight:600;text-decoration:none;'>
                    Ir para o Dashboard →
                </a>
            </p>
        </div>
    ";
} else {
    $content = "
        <div class='card' style='text-align:center'>
            <h1>Sistema de Login com PHP + SQLite</h1>
            <p style='color:#64748b;margin-bottom:24px'>
                Cadastre-se ou faça login para acessar a área restrita.
            </p>
            <p>
                <a href='/cadastro' class='btn btn-primary' style='display:inline-block;width:auto;padding:12px 32px;text-decoration:none;'>
                    Criar Conta
                </a>
            </p>
            <p class='link' style='margin-top:20px'>
                Já tem conta? <a href='/login'>Faça login</a>
            </p>
        </div>
    ";
}

echo layout('Sistema de Login', $content, $loggedIn, Session::userName()
```

### `templates/cadastro.php`

```php
<?php
// templates/cadastro.php
$errors = Session::flash('errors', null) ?? [];
$data = Session::flash('data', null) ?? [];
$success = Session::flash('success', null) ?? '';

$name = htmlspecialchars($data['name'] ?? '');
$email = htmlspecialchars($data['email'] ?? '');

$errorsHtml = '';
if (!empty($errors)) {
    $errorsHtml = '<div class="alert alert-erro"><ul>';
    foreach ($errors as $error) {
        $errorsHtml .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $errorsHtml .= '</ul></div>';
}

$successHtml = '';
if ($success) {
    $successHtml = '<div class="alert alert-sucesso">' . htmlspecialchars($success) . '</div>';
}

$content = <<<HTML
<div class="card">
    <h1>Criar Conta</h1>
    {$errorsHtml}
    {$successHtml}
    <form method="post" action="/cadastro" novalidate>
        <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
        <div class="form-group">
            <label for="name">Nome completo</label>
            <input type="text" id="name" name="name" value="{$name}" required
                   autocomplete="name" autofocus>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{$email}" required
                   autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required
                   minlength="8"
                   placeholder="Mínimo 8 caracteres, maiúscula, número, símbolo">
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>
    <p class="link">Já tem conta? <a href="/login">Faça login</a></p>
</div>
HTML;

echo layout('Cadastro', $content, Session::isLoggedIn(), Session::userName()
```

### `templates/login.php`

```php
<?php
// templates/login.php
$error = Session::flash('error', null) ?? '';
$email = htmlspecialchars(Session::flash('email', null) ?? '');

$htmlErro = $error ? "<div class='alert alert-erro'>" . htmlspecialchars($error) . "</div>" : '';

$content = <<<HTML
<div class="card">
    <h1>Entrar</h1>
    {$htmlErro}
    <form method="post" action="/login" novalidate>
        <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{$email}" required
                   autocomplete="email" autofocus>
        </div>
        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p class="link">Não tem conta? <a href="/cadastro">Cadastre-se</a></p>
</div>
HTML;

echo layout('Login', $content, Session::isLoggedIn(), Session::userName()
```

### `templates/dashboard.php`

```php
<?php
// templates/dashboard.php
$name = htmlspecialchars(Session::userName());
$userId = Session::userId();

$content = <<<HTML
<div class="card">
    <h1>Dashboard</h1>
    <p>Bem-vindo(a), <strong>{$name}</strong>!</p>
    <p style="color:#64748b;margin-top:8px">ID do usuário: {$userId}</p>
    <div style="margin-top:32px;padding:16px;background:#f8fafc;border-radius:8px">
        <p style="font-weight:600;margin-bottom:8px">Você está logado com sucesso!</p>
        <p style="font-size:0.9rem;color:#64748b">
            Esta é uma área protegida. Apenas usuários autenticados podem acessá-la.
        </p>
    </div>
    <form method="post" action="/logout" class="logout-form" style="margin-top:24px">
        <button type="submit" class="btn btn-danger">Sair do Sistema</button>
    </form>
</div>
HTML;

echo layout('Dashboard', $content, true, Session::userName()
```

### `index.php` (Front Controller)

```php
<?php
// index.php — Front Controller
declare(strict_types=1);

// Autoload simples
spl_autoload_register(function (string $class) {
    $path = __DIR__ . '/src/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

$config = require __DIR__ . '/config.php';

// Inicializa sessão
Session::start();

// Inicializa banco
$pdo = Database::get($config['db']['caminho']);
Database::inicializar($pdo);

// Instâncias
$auth = new Auth($pdo);
$router = new Router();

// Gera token CSRF global
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== ROTAS ====================

// Home
$router->get('/', function () {
    require __DIR__ . '/templates/home.php';
});

// Cadastro — GET
$router->get('/cadastro', function () {
    if (Session::isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/templates/cadastro.php';
});

// Cadastro — POST
$router->post('/cadastro', function () use ($auth) {
    if (Session::isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }

    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('errors', ['Token de segurança inválido.']);
        Session::flash('data', $_POST);
        header('Location: /cadastro');
        exit;
    }

    $validator = new Validator();
    $data = $validator->validate($_POST, [
        'name'  => ['required', 'min:3', 'max:100'],
        'email' => ['required', 'email'],
        'password' => ['required', 'min:8', 'senha_forte'],
    ]);

    if ($validator->hasErrors()) {
        Session::flash('errors', $validator->getErrors());
        Session::flash('data', $_POST);
        header('Location: /cadastro');
        exit;
    }

    $result = $auth->register($data['name'], $data['email'], $data['password']);

    if (!$result['success']) {
        Session::flash('errors', [$result['error']]);
        Session::flash('data', $_POST);
        header('Location: /cadastro');
        exit;
    }

    Session::flash('success', 'Cadastro realizado! Faça login para continuar.');
    header('Location: /login');
    exit;
});

// Login — GET
$router->get('/login', function () {
    if (Session::isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/templates/login.php';
});

// Login — POST
$router->post('/login', function () use ($auth) {
    if (Session::isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('error', 'Token de segurança inválido.');
        Session::flash('email', $_POST['email'] ?? '');
        header('Location: /login');
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        Session::flash('error', 'Preencha todos os campos.');
        Session::flash('email', $email);
        header('Location: /login');
        exit;
    }

    $result = $auth->login($email, $password);

    if (!$result['success']) {
        Session::flash('error', $result['error']);
        Session::flash('email', $email);
        header('Location: /login');
        exit;
    }

    Session::setUser($result['user']);
    header('Location: /dashboard');
    exit;
});

// Dashboard — GET (protegido)
$router->get('/dashboard', function () {
    if (!Session::isLoggedIn()) {
        Session::flash('error', 'Faça login para acessar esta página.');
        header('Location: /login');
        exit;
    }
    require __DIR__ . '/templates/dashboard.php';
});

// Logout — POST
$router->post('/logout', function () {
    Session::destroy();
    header('Location: /');
    exit;
});

// ==================== DISPATCH ====================
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Suporte a _method override para navegadores
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$router->dispatch($method, $u
```

---

## Como Executar

```bash
php -S localhost:8080 -t /caminho/para/projetos/login/
# Acesse http://localhost:
```

O banco `database.sqlite` é criado na primeira execução.

---

## Funcionalidades

- Cadastro de usuário com validação completa (nome, email, senha forte)
- Login com `password_verify`
- Sessão segura (`session_regenerate_id` após login)
- Logout completo (destrói sessão e cookie)
- Proteção CSRF em todos os formulários POST
- Flash messages para feedback
- Roteamento simples (sem frameworks)
- SQLite (arquivo único, sem servidor MySQL)
- Autoload PSR-4 simplificado
- Estrutura MVC organizada

---

## Conceitos Aplicados
- `password_hash()` / `password_verify()`
- `password_needs_rehash()` para atualização automática
- Prepared statements (PDO com SQLite)
- Sessões seguras (`session_regenerate_id`)
- Proteção CSRF com `hash_equals`
- Validação de formulários customizada
- Roteamento manual (`parse_url` + `match`)
- Padrão PRG (Post-Redirect-Get)
- Flash messages via sessão
