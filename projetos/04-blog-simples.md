# Projeto 04: Blog Simples (CRUD Completo)

## Objetivo

Criar um blog completo com PHP puro + SQLite + PDO. Suporte a CRUD de posts, upload de imagem de capa, roteamento manual, templates PHP puro e proteção completa contra XSS, SQL injection e CSRF.

## Estrutura de Diretórios


```
projetos/blog/
    public/
        index.php          (front controller)
        .htaccess           (Apache rewrite, opcional)
    src/
        Database.php        (conexão PDO SQLite + schema)
        Router.php          (roteador)
        Session.php         (gerenciamento de sessão/flash)
        Auth.php            (autenticação)
        PostRepository.php  (CRUD de posts)
        functions.php       (funções auxiliares)
        upload.php          (upload seguro)
    templates/
        layout.php          (layout HTML base + CSS)
        home.php            (listagem de posts)
        post.php            (post individual)
        create.php           (formulário de criação)
        editar.php          (formulário de edição)
        login.php           (login admin)
    uploads/                (imagens de capa)

```

---

## Código de Cada Arquivo

### `public/.htaccess`

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

```

### `public/index.php`

```php
<?php
// public/index.php — Front Controller
declare(strict_types=1);

// Define o diretório raiz do projeto
define('ROOT_DIR', dirname(__DIR__));

// Autoload
spl_autoload_register(function (string $class) {
    $path = ROOT_DIR . '/src/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// Funções helpers
require_once ROOT_DIR . '/src/functions.php';

// Inicializa sessão
Session::start();

// Inicializa banco
$pdo = Database::get(ROOT_DIR . '/database.sqlite');
Database::inicializar($pdo);

// Instâncias
$auth = new Auth($pdo);
$postRepo = new PostRepository($pdo);
$router = new Router();

// Token CSRF global
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Admin padrão (criado na primeira execução)
$auth->createDefaultAdmin();

// ==================== ROTAS PÚBLICAS ====================

$router->get('/', function () use ($postRepo) {
    $page = max(1, (int) ($_GET['pagina'] ?? 1));
    $perPage = 6;
    $posts = $postRepo->findAll($page, $perPage);
    $total = $postRepo->count();
    $totalPages = (int) ceil($total / $perPage);
    require ROOT_DIR . '/templates/home.php';
});

$router->get('/post', function () use ($postRepo) {
    $id = (int) ($_GET['id'] ?? 0);
    $post = $postRepo->findById($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Post não encontrado', '<div class="card"><h1>404 — Post não encontrado</h1><p><a href="/">Voltar para Home</a></p></div>');
        return;
    }

    require ROOT_DIR . '/templates/post.php';
});

// ==================== ROTAS DE ADMIN ====================

$router->get('/admin/login', function () {
    if (Session::isLoggedIn()) {
        header('Location: /admin');
        exit;
    }
    require ROOT_DIR . '/templates/login.php';
});

$router->post('/admin/login', function () use ($auth) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        Session::flash('error', 'Preencha todos os campos.');
        Session::flash('email', $email);
        header('Location: /admin/login');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('error', 'Token de segurança inválido.');
        header('Location: /admin/login');
        exit;
    }

    $result = $auth->login($email, $password);

    if (!$result['success']) {
        Session::flash('error', $result['error']);
        Session::flash('email', $email);
        header('Location: /admin/login');
        exit;
    }

    Session::setUser($result['user']);
    header('Location: /admin');
    exit;
});

// Painel admin
$router->get('/admin', function () use ($postRepo) {
    requireLogin();
    $posts = $postRepo->findAll(1, 100);
    require ROOT_DIR . '/templates/admin.php';
});

// Criar post — GET
$router->get('/admin/create', function () {
    requireLogin();
    $data = Session::flash('data', null) ?? [];
    $errors = Session::flash('errors', null) ?? [];
    require ROOT_DIR . '/templates/create.php';
});

// Criar post — POST
$router->post('/admin/create', function () use ($postRepo) {
    requireLogin();
    verifyCSRF();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $summary = trim($_POST['summary'] ?? '');

    $errors = [];
    if ($title === '') $errors[] = 'O título é obrigatório.';
    if (strlen($title) > 200) $errors[] = 'Título deve ter no máximo 200 caracteres.';
    if ($content === '') $errors[] = 'O conteúdo é obrigatório.';

    if (!empty($errors)) {
        Session::flash('errors', $errors);
        Session::flash('data', $_POST);
        header('Location: /admin/create');
        exit;
    }

    $cover = null;
    if (!empty($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $resultUpload = uploadCoverImage($_FILES['cover']);
        if ($resultUpload['success']) {
            $cover = $resultUpload['final_name'];
        } else {
            Session::flash('errors', $resultUpload['errors']);
            Session::flash('data', $_POST);
            header('Location: /admin/create');
            exit;
        }
    }

    $id = $postRepo->insert([
        'title'   => $title,
        'content' => $content,
        'summary' => $summary,
        'cover'   => $cover,
    ]);

    Session::flash('success', 'Post criado com sucesso!');
    header('Location: /post?id=' . $id);
    exit;
});

// Editar post — GET
$router->get('/admin/editar', function () use ($postRepo) {
    requireLogin();
    $id = (int) ($_GET['id'] ?? 0);
    $post = $postRepo->findById($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Erro', '<div class="card"><h1>Post não encontrado</h1></div>');
        return;
    }

    $data = Session::flash('data', null) ?? $post;
    $errors = Session::flash('errors', null) ?? [];
    require ROOT_DIR . '/templates/editar.php';
});

// Editar post — POST
$router->post('/admin/editar', function () use ($postRepo) {
    requireLogin();
    verifyCSRF();

    $id = (int) ($_POST['id'] ?? 0);
    $post = $postRepo->findById($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Erro', '<div class="card"><h1>Post não encontrado</h1></div>');
        return;
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $summary = trim($_POST['summary'] ?? '');

    $errors = [];
    if ($title === '') $errors[] = 'O título é obrigatório.';
    if ($content === '') $errors[] = 'O conteúdo é obrigatório.';

    if (!empty($errors)) {
        Session::flash('errors', $errors);
        Session::flash('data', $_POST);
        header('Location: /admin/editar?id=' . $id);
        exit;
    }

    $data = [
        'title'   => $title,
        'content' => $content,
        'summary' => $summary,
    ];

    if (!empty($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $resultUpload = uploadCoverImage($_FILES['cover']);
        if ($resultUpload['success']) {
            $data['cover'] = $resultUpload['final_name'];
        } else {
            Session::flash('errors', $resultUpload['errors']);
            Session::flash('data', $_POST);
            header('Location: /admin/editar?id=' . $id);
            exit;
        }
    }

    $postRepo->update($id, $data);
    Session::flash('success', 'Post atualizado com sucesso!');
    header('Location: /post?id=' . $id);
    exit;
});

// Excluir post
$router->post('/admin/excluir', function () use ($postRepo) {
    requireLogin();
    verifyCSRF();

    $id = (int) ($_POST['id'] ?? 0);
    $postRepo->delete($id);

    Session::flash('success', 'Post excluído com sucesso!');
    $_SESSION['flash_type'] = 'success';
    header('Location: /admin');
    exit;
});

// Logout
$router->post('/admin/logout', function () {
    Session::destroy();
    header('Location: /');
    exit;
});

// 404
$router->get('/{qualquer}', function () {
    http_response_code(404);
    echo layout('404', '<div class="card" style="text-align:center"><h1>404</h1><p>Página não encontrada.</p><p><a href="/">Voltar para Home</a></p></div>');
});

// ==================== DISPATCH ====================
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI
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
            CREATE TABLE IF NOT EXISTS posts (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                title       TEXT    NOT NULL,
                content     TEXT    NOT NULL,
                summary     TEXT    DEFAULT '',
                cover       TEXT,
                created_at  TEXT    DEFAULT (datetime('now', 'localtime')),
                updated_at  TEXT    DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS users (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                name     TEXT    NOT NULL,
                email    TEXT    NOT NULL UNIQUE,
                password    TEXT    NOT NULL,
                created_at TEXT   DEFAULT (datetime('now', 'localtime'))
            );
        ");
   
```

### `src/Router.php`

```php
<?php
// src/Router.php
class Router {
    private array $routes = [];
    private array $patterns = [];

    public function get(string $path, callable $handler): void {
        if (str_contains($path, '{') && str_contains($path, '}')) {
            $this->patterns['GET'][] = ['pattern' => $path, 'handler' => $handler];
        } else {
            $this->routes['GET'][$path] = $handler;
        }
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
            return;
        }

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            if (isset($this->routes[$method][$uri])) {
                call_user_func($this->routes[$method][$uri]);
                return;
            }
        }

        foreach ($this->patterns[$method] ?? [] as $route) {
            $regex = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $route['pattern']) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                call_user_func($route['handler'], ...$matches);
                return;
            }
        }

        http_response_code(404);
        echo "<h1>404 — Página não encontrada</h1>";
   
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
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('BLOG_SID');
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

    public static function flash(string $key, mixed $value = null): mixed {
        if (func_num_args() === 2) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function setUser(array $user): void {
        self::set('user_id', $user['id']);
        self::set('user_name', $user['name']);
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool {
        return self::get('user_id') !== null;
    }

    public static function userName(): ?string {
        return self::get('user_name');
   
```

### `src/Auth.php`

```php
<?php
// src/Auth.php
class Auth {
    public function __construct(private PDO $pdo) {}

    public function login(string $email, string $password): array {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Email ou password incorretos.'];
        }

        unset($user['password']);
        return ['success' => true, 'user' => $user];
    }

    public function createDefaultAdmin(): void {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        if ($stmt->fetchColumn() == 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $this->pdo->prepare(
                'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
            )->execute(['Administrador', 'admin@blog.com', $hash]);
        }
   
```

### `src/PostRepository.php`

```php
<?php
// src/PostRepository.php
class PostRepository {
    public function __construct(private PDO $pdo) {}

    public function findAll(int $page = 1, int $perPage = 10): array {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT * FROM posts ORDER BY created_at DESC LIMIT :limite OFFSET :offset'
        );
        $stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function insert(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (title, content, summary, cover) VALUES (:title, :content, :summary, :cover)'
        );
        $stmt->execute([
            ':title'   => $data['title'],
            ':content' => $data['content'],
            ':summary'   => $data['summary'] ?? '',
            ':cover'     => $data['cover'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $sets = [];
        $params = [':id' => $id];

        foreach (['title', 'content', 'summary', 'cover'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        $sets[] = "updated_at = datetime('now', 'localtime')";
        $sql = 'UPDATE posts SET ' . implode(', ', $sets) . ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
   
```

### `src/functions.php`

```php
<?php
// src/functions.php

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function verifyCSRF(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function requireLogin(): void {
    if (!Session::isLoggedIn()) {
        Session::flash('error', 'Faça login para acessar.');
        header('Location: /admin/login');
        exit;
    }
}

function uploadCoverImage(array $file): array {
    $maxFileSize = 5 * 1024 * 1024;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'errors' => ['Erro no upload.']];
    }

    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'errors' => ['Imagem maior que 5 MB.']];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        return ['success' => false, 'errors' => ['Tipo de imagem não permitido.']];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = ROOT_DIR . '/uploads/' . $fileName;

    $dirUploads = ROOT_DIR . '/uploads';
    if (!is_dir($dirUploads)) {
        mkdir($dirUploads, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'final_name' => $fileName];
    }

    return ['success' => false, 'errors' => ['Falha ao salvar imagem.']];
}

function truncateText(string $text, int $limit = 200): string {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '...';
}

function formatDate(string $date): string {
    $timestamp = strtotime($date);
    return date('d/m/Y \à\s H:i', $timestamp);
}

function coverUrl(?string $cover): string {
    if ($cover && file_exists(ROOT_DIR . '/uploads/' . $cover)) {
        return '/uploads/' . $cover;
    }
    return ''; // sem imag
```

### `src/upload.php`

```php
<?php
// src/upload.php — placeholder (a lógica está em functions.php)
// Este arquivo existe para organização, mas o upload é tratado em functions.php:uploadCoverIma
```

### `templates/layout.php`

```php
<?php
// templates/layout.php
function layout(string $title, string $content, bool $loggedIn = false): string {
    $adminLink = $loggedIn
        ? '<a href="/admin">Painel</a> | <a href="/admin/logout" onclick="document.getElementById(\'logout-form\').submit();return false;">Sair</a>'
        : '<a href="/admin/login">Admin</a>';

    $csrfToken = h($_SESSION['csrf_token'] ?? '');

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} — Blog PHP</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        header .logo { font-size: 1.25rem; font-weight: 800; color: #6366f1; text-decoration: none; }
        header nav { display: flex; gap: 20px; align-items: center; }
        header nav a { color: #64748b; text-decoration: none; font-size: 0.9rem; transition: color 0.15s; }
        header nav a:hover { color: #6366f1; }
        main { flex: 1; max-width: 900px; width: 100%; margin: 0 auto; padding: 32px 20px; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }
        .post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            transition: box-shadow 0.2s;
        }
        .post-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        .post-card .capa { width: 100%; height: 220px; object-fit: cover; background: #e2e8f0; }
        .post-card .corpo { padding: 20px; }
        .post-card h2 { font-size: 1.3rem; margin-bottom: 8px; }
        .post-card h2 a { color: #1e293b; text-decoration: none; }
        .post-card h2 a:hover { color: #6366f1; }
        .post-card .meta { color: #94a3b8; font-size: 0.8rem; margin-bottom: 10px; }
        .post-card .resumo { color: #475569; font-size: 0.95rem; }
        .post-header { margin-bottom: 24px; }
        .post-header h1 { font-size: 2rem; margin-bottom: 12px; }
        .post-header .meta { color: #94a3b8; font-size: 0.9rem; }
        .post-header .capa-full { width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px;
                                  margin: 16px 0; }
        .post-content { font-size: 1.05rem; line-height: 1.8; }
        .post-content p { margin-bottom: 1rem; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
        .alert-erro { background: #fee2e2; color: #991b1b; }
        .alert-sucesso { background: #d1fae5; color: #065f46; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group textarea { min-height: 200px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-primary { background: #6366f1; color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fee2e2; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .paginacao { display: flex; gap: 8px; justify-content: center; margin-top: 32px; }
        .paginacao a, .paginacao span {
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #e2e8f0;
            color: #475569;
            background: white;
        }
        .paginacao a:hover { border-color: #6366f1; color: #6366f1; }
        .paginacao .ativo { background: #6366f1; color: white; border-color: #6366f1; }
        .tabela { width: 100%; border-collapse: collapse; }
        .tabela th, .tabela td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .tabela th { font-size: 0.8rem; text-transform: uppercase; color: #94a3b8; }
        .acoes { display: flex; gap: 8px; }
        footer { text-align: center; padding: 24px; color: #94a3b8; font-size: 0.8rem; border-top: 1px solid #e2e8f0; }
        .vazio { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .mt-16 { margin-top: 16px; }
        .mb-16 { margin-bottom: 16px; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <header>
        <a href="/" class="logo">📝 Blog PHP</a>
        <nav>
            <a href="/">Home</a>
            {$adminLink}
        </nav>
    </header>
    <main>
        {$content}
    </main>
    <footer>
        Blog construído com PHP Puro + SQLite &mdash; 2026
    </footer>
    <!-- Form oculto para logout -->
    <form id="logout-form" method="post" action="/admin/logout" style="display:none">
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
    </form>
</body>
</html>
HTM
```

### `templates/home.php`

```php
<?php
// templates/home.php (já com $posts, $page, $totalPages)

$content = '';
$loggedIn = Session::isLoggedIn();

// Mensagem flash
$success = Session::flash('success');
if ($success) {
    $content .= '<div class="alert alert-sucesso">' . h($success) . '</div>';
}

if (empty($posts)) {
    $content .= '<div class="vazio"><h2>Nenhum post ainda.</h2><p>O administrador ainda não publicou conteúdo.</p></div>';
} else {
    foreach ($posts as $post) {
        $coverUrl = coverUrl($post['cover']);
        $coverHtml = $coverUrl ? '<img src="' . h($coverUrl) . '" alt="' . h($post['title']) . '" class="capa">' : '';
        $summary = h($post['summary'] ?: truncateText($post['content']));

        $content .= <<<POST
        <article class="post-card">
            {$coverHtml}
            <div class="corpo">
                <h2><a href="/post?id={$post['id']}">{$post['title']}</a></h2>
                <div class="meta">Publicado em {$post['created_at']}</div>
                <p class="resumo">{$summary}</p>
                <a href="/post?id={$post['id']}" style="color:#6366f1;font-weight:600;text-decoration:none;font-size:0.9rem;margin-top:8px;display:inline-block;">Ler mais →</a>
            </div>
        </article>
        POST;
    }

    // Paginação
    if ($totalPages > 1) {
        $content .= '<div class="paginacao">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $class = ($i === $page) ? 'ativo' : '';
            $content .= "<a href='/?pagina={$i}' class='{$class}'>{$i}</a>";
        }
        $content .= '</div>';
    }
}

echo layout('Home', $content, $logged
```

### `templates/post.php`

```php
<?php
// templates/post.php (já com $post definido)
$loggedIn = Session::isLoggedIn();
$coverUrl = coverUrl($post['cover']);
$coverHtml = $coverUrl ? "<img src='" . h($coverUrl) . "' alt='" . h($post['title']) . "' class='capa-full'>" : '';
$date = formatDate($post['created_at']);
$postContent = nl2br(h($post['content']));

$adminButtons = '';
if ($loggedIn) {
    $csrfToken = $_SESSION['csrf_token'];
    $adminButtons = <<<BTN
    <div class="acoes mt-16">
        <a href="/admin/editar?id={$post['id']}" class="btn btn-secondary btn-sm">Editar</a>
        <form method="post" action="/admin/excluir" style="display:inline"
              onsubmit="return confirm('Excluir este post?')">
            <input type="hidden" name="csrf_token" value="{$csrfToken}">
            <input type="hidden" name="id" value="{$post['id']}">
            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
        </form>
    </div>
    BTN;
}

$content = <<<HTML
<article>
    <div class="card">
        <a href="/" style="color:#6366f1;text-decoration:none;font-size:0.9rem;">← Voltar</a>

        <div class="post-header">
            <h1>{$post['title']}</h1>
            <div class="meta">Publicado em {$data}</div>
            {$coverHtml}
        </div>

        <div class="post-content">
            {$postContent}
        </div>

        {$adminButtons}
    </div>
</article>
HTML;

echo layout('Post: ' . $post['title'], $content, $logged
```

### `templates/create.php`

```php
<?php
// templates/create.php
$data = $data ?? [];
$errors = $errors ?? [];
$loggedIn = Session::isLoggedIn();

$errorHtml = '';
if (!empty($errors)) {
    $errorHtml = '<div class="alert alert-erro"><ul>';
    foreach ($errors as $error) {
        $errorHtml .= '<li>' . h($error) . '</li>';
    }
    $errorHtml .= '</ul></div>';
}

$csrfToken = $_SESSION['csrf_token'];
$title = h($data['title'] ?? '');
$summary = h($data['summary'] ?? '');
$postContent = h($data['content'] ?? '');

$html = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Novo Post</h1>
        <a href="/admin" class="btn btn-secondary btn-sm">← Voltar</a>
    </div>
    {$errorHtml}
    <form method="post" action="/admin/create" enctype="multipart/form-data">
        {$csrfToken}
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" id="title" name="title" value="{$title}" required maxlength="200">
        </div>
        <div class="form-group">
            <label for="summary">Resumo (opcional)</label>
            <input type="text" id="summary" name="summary" value="{$summary}" maxlength="500">
        </div>
        <div class="form-group">
            <label for="cover">Imagem de Capa (opcional)</label>
            <input type="file" id="cover" name="cover" accept="image/*">
        </div>
        <div class="form-group">
            <label for="content">Conteúdo</label>
            <textarea id="content" name="content" required>{$postContent}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Publicar Post</button>
    </form>
</div>
HTML;

echo layout('Novo Post', $html, $logged
```

### `templates/editar.php`

```php
<?php
// templates/editar.php
$errors = $errors ?? [];
$loggedIn = Session::isLoggedIn();

$errorHtml = '';
if (!empty($errors)) {
    $errorHtml = '<div class="alert alert-erro"><ul>';
    foreach ($errors as $error) {
        $errorHtml .= '<li>' . h($error) . '</li>';
    }
    $errorHtml .= '</ul></div>';
}

$csrfToken = $_SESSION['csrf_token'];
$title = h($data['title'] ?? $post['title'] ?? '');
$summary = h($data['summary'] ?? $post['summary'] ?? '');
$postContent = h($data['content'] ?? $post['content'] ?? '');
$currentCover = coverUrl($post['cover'] ?? null);
$coverInfo = $currentCover ? "<p style='font-size:0.85rem;color:#64748b;margin-top:4px'>Capa atual: " . h($post['cover']) . "</p>" : '';

$html = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Editar Post</h1>
        <a href="/post?id={$post['id']}" class="btn btn-secondary btn-sm">← Cancelar</a>
    </div>
    {$errorHtml}
    <form method="post" action="/admin/editar" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <input type="hidden" name="id" value="{$post['id']}">
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" id="title" name="title" value="{$title}" required maxlength="200">
        </div>
        <div class="form-group">
            <label for="summary">Resumo (opcional)</label>
            <input type="text" id="summary" name="summary" value="{$summary}" maxlength="500">
        </div>
        <div class="form-group">
            <label for="cover">Imagem de Capa (substituir)</label>
            <input type="file" id="cover" name="cover" accept="image/*">
            {$coverInfo}
        </div>
        <div class="form-group">
            <label for="content">Conteúdo</label>
            <textarea id="content" name="content" required>{$postContent}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
HTML;

echo layout('Editar Post', $html, $logged
```

### `templates/login.php`

```php
<?php
// templates/login.php
$error = Session::flash('error');
$emailValue = h(Session::flash('email', null) ?? '');
$csrfToken = $_SESSION['csrf_token'];

$errorHtml = $error ? '<div class="alert alert-erro">' . h($error) . '</div>' : '';

$content = <<<HTML
<div class="card" style="max-width:420px;margin:40px auto;">
    <h1>Login — Administração</h1>
    {$errorHtml}
    <form method="post" action="/admin/login">
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{$emailValue}" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:0.85rem;color:#94a3b8;">
        Admin padrão: admin@blog.com / admin123
    </p>
</div>
HTML;

echo layout('Login Admin', $content, Session::isLoggedIn()
```

### `templates/admin.php` (Painel)

```php
<?php
// templates/admin.php (já com $posts)
$loggedIn = Session::isLoggedIn();
$csrfToken = $_SESSION['csrf_token'];

$success = Session::flash('success');
$flashHtml = $success ? '<div class="alert alert-sucesso">' . h($success) . '</div>' : '';

$lines = '';
foreach ($posts as $post) {
    $date = formatDate($post['created_at']);
    $title = h($post['title']);
    $lines .= <<<ROW
    <tr>
        <td>{$post['id']}</td>
        <td><a href="/post?id={$post['id']}" style="color:#1e293b;text-decoration:none;">{$title}</a></td>
        <td>{$data}</td>
        <td>
            <div class="acoes">
                <a href="/admin/editar?id={$post['id']}" class="btn btn-secondary btn-sm">Editar</a>
                <form method="post" action="/admin/excluir" style="display:inline"
                      onsubmit="return confirm('Excluir este post?')">
                    <input type="hidden" name="csrf_token" value="{$csrfToken}">
                    <input type="hidden" name="id" value="{$post['id']}">
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </div>
        </td>
    </tr>
    ROW;
}

$content = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Painel de Administração</h1>
        <a href="/admin/create" class="btn btn-primary">+ Novo Post</a>
    </div>
    {$flashHtml}
    <table class="tabela">
        <thead>
            <tr><th>ID</th><th>Título</th><th>Data</th><th>Ações</th></tr>
        </thead>
        <tbody>
            {$lines}
        </tbody>
    </table>
    <p style="text-align:center;color:#94a3b8;margin-top:16px;">
        Total de posts: {count($posts)}
    </p>
</div>
HTML;

echo layout('Painel Admin', $content, $logged
```

---

## Como Executar

```bash
# 1. Acesse o diretório public/
cd /caminho/para/projetos/blog/public

# 2. Inicie o servidor PHP
php -S localhost:8080

# 3. Acesse http://localhost:
```

**Credenciais do admin (criadas na primeira execução):**
- Email: `admin@blog.com`
- Senha: `admin123`

---

## Funcionalidades

- **CRUD completo** de posts (criar, ler, atualizar, deletar)
- **Upload de imagem** de capa com validação
- **Listagem** com paginação (6 posts por página)
- **Página individual** de cada post
- **Painel admin** protegido por login
- **CSRF** em todos os formulários POST
- **SQLite + PDO** com prepared statements
- **Templates PHP puro** (sem engine de template)
- **Router manual** com suporte a parâmetros dinâmicos
- **Flash messages** para feedback
- **Design responsivo** com CSS puro

---

## Conceitos Aplicados
- MVC simplificado (Router → Controller → Repository/Template)
- PDO com prepared statements (anti-SQL injection)
- `htmlspecialchars()` em todo output (anti-XSS)
- `hash_equals()` para verificação CSRF
- `password_hash()` / `password_verify()` para login
- Upload seguro (`finfo`, renomear arquivos)
- Padrão PRG (Post-Redirect-Get)
- Sessões com `session_regenerate_id`
- Paginação com `LIMIT/OFFSET`
- Autoload PSR-4 simplificado
- Front controller com roteamento
