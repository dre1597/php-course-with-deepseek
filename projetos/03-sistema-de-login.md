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
        'nome' => 'Sistema de Login',
        'url'  => 'http://localhost:8080',
    ],
    'db' => [
        'caminho' => __DIR__ . '/database.sqlite',
    ],
    'senha' => [
        'algoritmo' => PASSWORD_DEFAULT, // PASSWORD_BCRYPT ou PASSWORD_ARGON2ID
        'custo'     => 12,               // PHP 8.4+: padrão subiu para 12
    ],
];
```

### `src/Database.php`

```php
<?php
// src/Database.php
class Database {
    private static ?PDO $instancia = null;

    public static function get(string $caminho): PDO {
        if (self::$instancia === null) {
            self::$instancia = new PDO(
                'sqlite:' . $caminho,
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
            CREATE TABLE IF NOT EXISTS usuarios (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                nome            TEXT    NOT NULL,
                email           TEXT    NOT NULL UNIQUE,
                senha           TEXT    NOT NULL,
                token_lembrar   TEXT,
                criado_em       TEXT    DEFAULT (datetime('now', 'localtime')),
                atualizado_em   TEXT    DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS tokens_csrf (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id  INTEGER NOT NULL,
                token       TEXT    NOT NULL,
                criado_em   TEXT    DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            );

            CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
        ");
    }
}
```

### `src/Session.php`

```php
<?php
// src/Session.php
class Session {
    public static function iniciar(): void {
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

    public static function set(string $chave, mixed $valor): void {
        $_SESSION[$chave] = $valor;
    }

    public static function get(string $chave, mixed $padrao = null): mixed {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function remover(string $chave): void {
        unset($_SESSION[$chave]);
    }

    public static function flash(string $chave, string $mensagem = null): ?string {
        if ($mensagem !== null) {
            $_SESSION['_flash'][$chave] = $mensagem;
            return null;
        }
        $msg = $_SESSION['_flash'][$chave] ?? null;
        unset($_SESSION['_flash'][$chave]);
        return $msg;
    }

    public static function destruir(): void {
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

    public static function setUsuario(array $usuario): void {
        self::set('usuario_id', $usuario['id']);
        self::set('usuario_nome', $usuario['nome']);
        self::set('usuario_email', $usuario['email']);
        session_regenerate_id(true);
    }

    public static function estaLogado(): bool {
        return self::get('usuario_id') !== null;
    }

    public static function usuarioId(): ?int {
        return self::get('usuario_id');
    }

    public static function usuarioNome(): ?string {
        return self::get('usuario_nome');
    }
}
```

### `src/Validator.php`

```php
<?php
// src/Validator.php
class Validator {
    private array $erros = [];

    public function validar(array $dados, array $regras): array {
        $limpos = [];

        foreach ($regras as $campo => $regrasCampo) {
            $valor = $dados[$campo] ?? '';

            if (in_array('required', $regrasCampo) && trim((string) $valor) === '') {
                $this->erros[$campo] = "O campo '{$campo}' é obrigatório.";
                continue;
            }

            $valor = trim((string) $valor);

            if (in_array('email', $regrasCampo) && $valor !== '') {
                $validado = filter_var($valor, FILTER_VALIDATE_EMAIL);
                if ($validado === false) {
                    $this->erros[$campo] = 'Email inválido.';
                } else {
                    $valor = $validado;
                }
            }

            foreach ($regrasCampo as $regra) {
                if (str_starts_with($regra, 'min:')) {
                    $min = (int) substr($regra, 4);
                    if (strlen($valor) < $min) {
                        $this->erros[$campo] = "Deve ter no mínimo {$min} caracteres.";
                    }
                }
                if (str_starts_with($regra, 'max:')) {
                    $max = (int) substr($regra, 4);
                    if (strlen($valor) > $max) {
                        $this->erros[$campo] = "Deve ter no máximo {$max} caracteres.";
                    }
                }
                if ($regra === 'senha_forte' && $valor !== '') {
                    if (!preg_match('/[A-Z]/', $valor)) {
                        $this->erros[$campo] = 'Deve conter ao menos uma maiúscula.';
                    }
                    if (!preg_match('/[a-z]/', $valor)) {
                        $this->erros[$campo] = 'Deve conter ao menos uma minúscula.';
                    }
                    if (!preg_match('/[0-9]/', $valor)) {
                        $this->erros[$campo] = 'Deve conter ao menos um número.';
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $valor)) {
                        $this->erros[$campo] = 'Deve conter ao menos um caractere especial.';
                    }
                }
            }

            $limpos[$campo] = $valor;
        }

        return $limpos;
    }

    public function temErros(): bool {
        return !empty($this->erros);
    }

    public function getErros(): array {
        return $this->erros;
    }
}
```

### `src/Auth.php`

```php
<?php
// src/Auth.php
class Auth {
    public function __construct(private PDO $pdo) {}

    public function cadastrar(string $nome, string $email, string $senha): array {
        // Verifica email duplicado
        $stmt = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            return ['sucesso' => false, 'erro' => 'Este email já está cadastrado.'];
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)'
        );
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':senha' => $hash,
        ]);

        return ['sucesso' => true, 'id' => (int) $this->pdo->lastInsertId()];
    }

    public function login(string $email, string $senha): array {
        $stmt = $this->pdo->prepare('SELECT id, nome, email, senha FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return ['sucesso' => false, 'erro' => 'Email ou senha incorretos.'];
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return ['sucesso' => false, 'erro' => 'Email ou senha incorretos.'];
        }

        unset($usuario['senha']);
        return ['sucesso' => true, 'usuario' => $usuario];
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, nome, email, criado_em FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function gerarTokenCSRF(int $usuarioId): string {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->pdo->prepare(
            'INSERT INTO tokens_csrf (usuario_id, token) VALUES (:usuario_id, :token)'
        );
        $stmt->execute([':usuario_id' => $usuarioId, ':token' => $token]);

        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public function verificarTokenCSRF(string $token): bool {
        $esperado = $_SESSION['csrf_token'] ?? '';
        return $esperado !== '' && hash_equals($esperado, $token);
    }
}
```

### `src/Router.php`

```php
<?php
// src/Router.php
class Router {
    private array $rotas = [];

    public function get(string $caminho, callable $handler): void {
        $this->rotas['GET'][$caminho] = $handler;
    }

    public function post(string $caminho, callable $handler): void {
        $this->rotas['POST'][$caminho] = $handler;
    }

    public function dispatch(string $metodo, string $uri): void {
        $metodo = strtoupper($metodo);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->rotas[$metodo][$uri])) {
            call_user_func($this->rotas[$metodo][$uri]);
        } else {
            http_response_code(404);
            echo "<h1>404 — Página não encontrada</h1>\n";
            echo "<p>Método: {$metodo} | URI: " . htmlspecialchars($uri) . "</p>";
        }
    }
}
```

### `templates/layout.php`

```php
<?php
// templates/layout.php
function layout(string $titulo, string $conteudo, bool $logado = false, string $nomeUsuario = ''): string {
    $nome = htmlspecialchars($nomeUsuario);
    $navLogado = $logado ? "
        <a href='/dashboard'>Dashboard</a>
        <a href='/logout'>Sair</a>
    " : "
        <a href='/login'>Entrar</a>
        <a href='/cadastro'>Cadastrar</a>
    ";
    $nomeLogado = $logado ? "<span class='user-name'>{$nome}</span>" : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titulo}</title>
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
            {$nomeLogado}
            {$navLogado}
        </div>
    </nav>
    <div class="container">
        {$conteudo}
    </div>
    <footer>&copy; 2026 — PHP Puro + SQLite</footer>
</body>
</html>
HTML;
}
```

### `templates/home.php`

```php
<?php
// templates/home.php
$logado = Session::estaLogado();

if ($logado) {
    $conteudo = "
        <div class='card'>
            <h1>Bem-vindo(a), " . htmlspecialchars(Session::usuarioNome()) . "!</h1>
            <p>Você está logado no sistema.</p>
            <p style='margin-top:16px'>
                <a href='/dashboard' style='color:#6366f1;font-weight:600;text-decoration:none;'>
                    Ir para o Dashboard →
                </a>
            </p>
        </div>
    ";
} else {
    $conteudo = "
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

echo layout('Sistema de Login', $conteudo, $logado, Session::usuarioNome());
```

### `templates/cadastro.php`

```php
<?php
// templates/cadastro.php
$erros = Session::flash('erros', null) ?? [];
$dados = Session::flash('dados', null) ?? [];
$sucesso = Session::flash('sucesso', null) ?? '';

$nome = htmlspecialchars($dados['nome'] ?? '');
$email = htmlspecialchars($dados['email'] ?? '');

$htmlErros = '';
if (!empty($erros)) {
    $htmlErros = '<div class="alert alert-erro"><ul>';
    foreach ($erros as $erro) {
        $htmlErros .= '<li>' . htmlspecialchars($erro) . '</li>';
    }
    $htmlErros .= '</ul></div>';
}

$htmlSucesso = '';
if ($sucesso) {
    $htmlSucesso = '<div class="alert alert-sucesso">' . htmlspecialchars($sucesso) . '</div>';
}

$conteudo = <<<HTML
<div class="card">
    <h1>Criar Conta</h1>
    {$htmlErros}
    {$htmlSucesso}
    <form method="post" action="/cadastro" novalidate>
        <input type="hidden" name="csrf_token" value="{$_SESSION['csrf_token']}">
        <div class="form-group">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" value="{$nome}" required
                   autocomplete="name" autofocus>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{$email}" required
                   autocomplete="email">
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required
                   minlength="8"
                   placeholder="Mínimo 8 caracteres, maiúscula, número, símbolo">
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>
    <p class="link">Já tem conta? <a href="/login">Faça login</a></p>
</div>
HTML;

echo layout('Cadastro', $conteudo, Session::estaLogado(), Session::usuarioNome());
```

### `templates/login.php`

```php
<?php
// templates/login.php
$erro = Session::flash('erro', null) ?? '';
$email = htmlspecialchars(Session::flash('email', null) ?? '');

$htmlErro = $erro ? "<div class='alert alert-erro'>" . htmlspecialchars($erro) . "</div>" : '';

$conteudo = <<<HTML
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
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p class="link">Não tem conta? <a href="/cadastro">Cadastre-se</a></p>
</div>
HTML;

echo layout('Login', $conteudo, Session::estaLogado(), Session::usuarioNome());
```

### `templates/dashboard.php`

```php
<?php
// templates/dashboard.php
$nome = htmlspecialchars(Session::usuarioNome());
$usuarioId = Session::usuarioId();

$conteudo = <<<HTML
<div class="card">
    <h1>Dashboard</h1>
    <p>Bem-vindo(a), <strong>{$nome}</strong>!</p>
    <p style="color:#64748b;margin-top:8px">ID do usuário: {$usuarioId}</p>
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

echo layout('Dashboard', $conteudo, true, Session::usuarioNome());
```

### `index.php` (Front Controller)

```php
<?php
// index.php — Front Controller
declare(strict_types=1);

// Autoload simples
spl_autoload_register(function (string $classe) {
    $caminho = __DIR__ . '/src/' . $classe . '.php';
    if (file_exists($caminho)) {
        require_once $caminho;
    }
});

$config = require __DIR__ . '/config.php';

// Inicializa sessão
Session::iniciar();

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
    if (Session::estaLogado()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/templates/cadastro.php';
});

// Cadastro — POST
$router->post('/cadastro', function () use ($auth) {
    if (Session::estaLogado()) {
        header('Location: /dashboard');
        exit;
    }

    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('erros', ['Token de segurança inválido.']);
        Session::flash('dados', $_POST);
        header('Location: /cadastro');
        exit;
    }

    $validator = new Validator();
    $dados = $validator->validar($_POST, [
        'nome'  => ['required', 'min:3', 'max:100'],
        'email' => ['required', 'email'],
        'senha' => ['required', 'min:8', 'senha_forte'],
    ]);

    if ($validator->temErros()) {
        Session::flash('erros', $validator->getErros());
        Session::flash('dados', $_POST);
        header('Location: /cadastro');
        exit;
    }

    $resultado = $auth->cadastrar($dados['nome'], $dados['email'], $dados['senha']);

    if (!$resultado['sucesso']) {
        Session::flash('erros', [$resultado['erro']]);
        Session::flash('dados', $_POST);
        header('Location: /cadastro');
        exit;
    }

    Session::flash('sucesso', 'Cadastro realizado! Faça login para continuar.');
    header('Location: /login');
    exit;
});

// Login — GET
$router->get('/login', function () {
    if (Session::estaLogado()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/templates/login.php';
});

// Login — POST
$router->post('/login', function () use ($auth) {
    if (Session::estaLogado()) {
        header('Location: /dashboard');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('erro', 'Token de segurança inválido.');
        Session::flash('email', $_POST['email'] ?? '');
        header('Location: /login');
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        Session::flash('erro', 'Preencha todos os campos.');
        Session::flash('email', $email);
        header('Location: /login');
        exit;
    }

    $resultado = $auth->login($email, $senha);

    if (!$resultado['sucesso']) {
        Session::flash('erro', $resultado['erro']);
        Session::flash('email', $email);
        header('Location: /login');
        exit;
    }

    Session::setUsuario($resultado['usuario']);
    header('Location: /dashboard');
    exit;
});

// Dashboard — GET (protegido)
$router->get('/dashboard', function () {
    if (!Session::estaLogado()) {
        Session::flash('erro', 'Faça login para acessar esta página.');
        header('Location: /login');
        exit;
    }
    require __DIR__ . '/templates/dashboard.php';
});

// Logout — POST
$router->post('/logout', function () {
    Session::destruir();
    header('Location: /');
    exit;
});

// ==================== DISPATCH ====================
$metodo = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Suporte a _method override para navegadores
if ($metodo === 'POST' && isset($_POST['_method'])) {
    $metodo = strtoupper($_POST['_method']);
}

$router->dispatch($metodo, $uri);
```

---

## Como Executar

```bash
php -S localhost:8080 -t /caminho/para/projetos/login/
# Acesse http://localhost:8080
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
