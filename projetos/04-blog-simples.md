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
        criar.php           (formulário de criação)
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
spl_autoload_register(function (string $classe) {
    $caminho = ROOT_DIR . '/src/' . $classe . '.php';
    if (file_exists($caminho)) {
        require_once $caminho;
    }
});

// Funções helpers
require_once ROOT_DIR . '/src/functions.php';

// Inicializa sessão
Session::iniciar();

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
$auth->criarAdminPadrao();

// ==================== ROTAS PÚBLICAS ====================

$router->get('/', function () use ($postRepo) {
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $porPagina = 6;
    $posts = $postRepo->buscarTodos($pagina, $porPagina);
    $total = $postRepo->contar();
    $totalPaginas = (int) ceil($total / $porPagina);
    require ROOT_DIR . '/templates/home.php';
});

$router->get('/post', function () use ($postRepo) {
    $id = (int) ($_GET['id'] ?? 0);
    $post = $postRepo->buscarPorId($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Post não encontrado', '<div class="card"><h1>404 — Post não encontrado</h1><p><a href="/">Voltar para Home</a></p></div>');
        return;
    }

    require ROOT_DIR . '/templates/post.php';
});

// ==================== ROTAS DE ADMIN ====================

$router->get('/admin/login', function () {
    if (Session::estaLogado()) {
        header('Location: /admin');
        exit;
    }
    require ROOT_DIR . '/templates/login.php';
});

$router->post('/admin/login', function () use ($auth) {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        Session::flash('erro', 'Preencha todos os campos.');
        Session::flash('email', $email);
        header('Location: /admin/login');
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        Session::flash('erro', 'Token de segurança inválido.');
        header('Location: /admin/login');
        exit;
    }

    $resultado = $auth->login($email, $senha);

    if (!$resultado['sucesso']) {
        Session::flash('erro', $resultado['erro']);
        Session::flash('email', $email);
        header('Location: /admin/login');
        exit;
    }

    Session::setUsuario($resultado['usuario']);
    header('Location: /admin');
    exit;
});

// Painel admin
$router->get('/admin', function () use ($postRepo) {
    requerLogin();
    $posts = $postRepo->buscarTodos(1, 100);
    require ROOT_DIR . '/templates/admin.php';
});

// Criar post — GET
$router->get('/admin/criar', function () {
    requerLogin();
    $dados = Session::flash('dados', null) ?? [];
    $erros = Session::flash('erros', null) ?? [];
    require ROOT_DIR . '/templates/criar.php';
});

// Criar post — POST
$router->post('/admin/criar', function () use ($postRepo) {
    requerLogin();
    verificarCSRF();

    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');

    $erros = [];
    if ($titulo === '') $erros[] = 'O título é obrigatório.';
    if (strlen($titulo) > 200) $erros[] = 'Título deve ter no máximo 200 caracteres.';
    if ($conteudo === '') $erros[] = 'O conteúdo é obrigatório.';

    if (!empty($erros)) {
        Session::flash('erros', $erros);
        Session::flash('dados', $_POST);
        header('Location: /admin/criar');
        exit;
    }

    // Upload de imagem
    $capa = null;
    if (!empty($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $resultadoUpload = uploadImagemCapa($_FILES['capa']);
        if ($resultadoUpload['sucesso']) {
            $capa = $resultadoUpload['nome_final'];
        } else {
            Session::flash('erros', $resultadoUpload['erros']);
            Session::flash('dados', $_POST);
            header('Location: /admin/criar');
            exit;
        }
    }

    $id = $postRepo->inserir([
        'titulo'   => $titulo,
        'conteudo' => $conteudo,
        'resumo'   => $resumo,
        'capa'     => $capa,
    ]);

    Session::flash('sucesso', 'Post criado com sucesso!');
    header('Location: /post?id=' . $id);
    exit;
});

// Editar post — GET
$router->get('/admin/editar', function () use ($postRepo) {
    requerLogin();
    $id = (int) ($_GET['id'] ?? 0);
    $post = $postRepo->buscarPorId($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Erro', '<div class="card"><h1>Post não encontrado</h1></div>');
        return;
    }

    $dados = Session::flash('dados', null) ?? $post;
    $erros = Session::flash('erros', null) ?? [];
    require ROOT_DIR . '/templates/editar.php';
});

// Editar post — POST
$router->post('/admin/editar', function () use ($postRepo) {
    requerLogin();
    verificarCSRF();

    $id = (int) ($_POST['id'] ?? 0);
    $post = $postRepo->buscarPorId($id);

    if (!$post) {
        http_response_code(404);
        echo layout('Erro', '<div class="card"><h1>Post não encontrado</h1></div>');
        return;
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');

    $erros = [];
    if ($titulo === '') $erros[] = 'O título é obrigatório.';
    if ($conteudo === '') $erros[] = 'O conteúdo é obrigatório.';

    if (!empty($erros)) {
        Session::flash('erros', $erros);
        Session::flash('dados', $_POST);
        header('Location: /admin/editar?id=' . $id);
        exit;
    }

    $dados = [
        'titulo'   => $titulo,
        'conteudo' => $conteudo,
        'resumo'   => $resumo,
    ];

    // Upload de nova capa
    if (!empty($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $resultadoUpload = uploadImagemCapa($_FILES['capa']);
        if ($resultadoUpload['sucesso']) {
            $dados['capa'] = $resultadoUpload['nome_final'];
        } else {
            Session::flash('erros', $resultadoUpload['erros']);
            Session::flash('dados', $_POST);
            header('Location: /admin/editar?id=' . $id);
            exit;
        }
    }

    $postRepo->atualizar($id, $dados);
    Session::flash('sucesso', 'Post atualizado com sucesso!');
    header('Location: /post?id=' . $id);
    exit;
});

// Excluir post
$router->post('/admin/excluir', function () use ($postRepo) {
    requerLogin();
    verificarCSRF();

    $id = (int) ($_POST['id'] ?? 0);
    $postRepo->deletar($id);

    Session::flash('sucesso', 'Post excluído com sucesso!');
    $_SESSION['flash_tipo'] = 'sucesso';
    header('Location: /admin');
    exit;
});

// Logout
$router->post('/admin/logout', function () {
    Session::destruir();
    header('Location: /');
    exit;
});

// 404
$router->get('/{qualquer}', function () {
    http_response_code(404);
    echo layout('404', '<div class="card" style="text-align:center"><h1>404</h1><p>Página não encontrada.</p><p><a href="/">Voltar para Home</a></p></div>');
});

// ==================== DISPATCH ====================
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
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
            CREATE TABLE IF NOT EXISTS posts (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo      TEXT    NOT NULL,
                conteudo    TEXT    NOT NULL,
                resumo      TEXT    DEFAULT '',
                capa        TEXT,
                criado_em   TEXT    DEFAULT (datetime('now', 'localtime')),
                atualizado_em TEXT  DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS usuarios (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                nome     TEXT    NOT NULL,
                email    TEXT    NOT NULL UNIQUE,
                senha    TEXT    NOT NULL,
                criado_em TEXT   DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }
}
```

### `src/Router.php`

```php
<?php
// src/Router.php
class Router {
    private array $rotas = [];
    private array $padroes = [];

    public function get(string $caminho, callable $handler): void {
        if (str_contains($caminho, '{') && str_contains($caminho, '}')) {
            $this->padroes['GET'][] = ['padrao' => $caminho, 'handler' => $handler];
        } else {
            $this->rotas['GET'][$caminho] = $handler;
        }
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
            return;
        }

        if ($metodo === 'POST' && isset($_POST['_method'])) {
            $metodo = strtoupper($_POST['_method']);
            if (isset($this->rotas[$metodo][$uri])) {
                call_user_func($this->rotas[$metodo][$uri]);
                return;
            }
        }

        foreach ($this->padroes[$metodo] ?? [] as $rota) {
            $regex = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $rota['padrao']) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                call_user_func($rota['handler'], ...$matches);
                return;
            }
        }

        http_response_code(404);
        echo "<h1>404 — Página não encontrada</h1>";
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
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('BLOG_SID');
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

    public static function flash(string $chave, mixed $valor = null): mixed {
        if (func_num_args() === 2) {
            $_SESSION['_flash'][$chave] = $valor;
            return null;
        }
        $v = $_SESSION['_flash'][$chave] ?? null;
        unset($_SESSION['_flash'][$chave]);
        return $v;
    }

    public static function destruir(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function setUsuario(array $usuario): void {
        self::set('usuario_id', $usuario['id']);
        self::set('usuario_nome', $usuario['nome']);
        session_regenerate_id(true);
    }

    public static function estaLogado(): bool {
        return self::get('usuario_id') !== null;
    }

    public static function usuarioNome(): ?string {
        return self::get('usuario_nome');
    }
}
```

### `src/Auth.php`

```php
<?php
// src/Auth.php
class Auth {
    public function __construct(private PDO $pdo) {}

    public function login(string $email, string $senha): array {
        $stmt = $this->pdo->prepare('SELECT id, nome, email, senha FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return ['sucesso' => false, 'erro' => 'Email ou senha incorretos.'];
        }

        unset($usuario['senha']);
        return ['sucesso' => true, 'usuario' => $usuario];
    }

    public function criarAdminPadrao(): void {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM usuarios');
        if ($stmt->fetchColumn() == 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $this->pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)'
            )->execute(['Administrador', 'admin@blog.com', $hash]);
        }
    }
}
```

### `src/PostRepository.php`

```php
<?php
// src/PostRepository.php
class PostRepository {
    public function __construct(private PDO $pdo) {}

    public function buscarTodos(int $pagina = 1, int $porPagina = 10): array {
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->pdo->prepare(
            'SELECT * FROM posts ORDER BY criado_em DESC LIMIT :limite OFFSET :offset'
        );
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function inserir(array $dados): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (titulo, conteudo, resumo, capa) VALUES (:titulo, :conteudo, :resumo, :capa)'
        );
        $stmt->execute([
            ':titulo'   => $dados['titulo'],
            ':conteudo' => $dados['conteudo'],
            ':resumo'   => $dados['resumo'] ?? '',
            ':capa'     => $dados['capa'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void {
        $sets = [];
        $params = [':id' => $id];

        foreach (['titulo', 'conteudo', 'resumo', 'capa'] as $campo) {
            if (array_key_exists($campo, $dados)) {
                $sets[] = "{$campo} = :{$campo}";
                $params[":{$campo}"] = $dados[$campo];
            }
        }

        $sets[] = "atualizado_em = datetime('now', 'localtime')";
        $sql = 'UPDATE posts SET ' . implode(', ', $sets) . ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function deletar(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function contar(): int {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }
}
```

### `src/functions.php`

```php
<?php
// src/functions.php

function h(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function verificarCSRF(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}

function campoCSRF(): string {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function requerLogin(): void {
    if (!Session::estaLogado()) {
        Session::flash('erro', 'Faça login para acessar.');
        header('Location: /admin/login');
        exit;
    }
}

function uploadImagemCapa(array $arquivo): array {
    $tamanhoMaximo = 5 * 1024 * 1024; // 5 MB
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'erros' => ['Erro no upload.']];
    }

    if ($arquivo['size'] > $tamanhoMaximo) {
        return ['sucesso' => false, 'erros' => ['Imagem maior que 5 MB.']];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $tiposPermitidos)) {
        return ['sucesso' => false, 'erros' => ['Tipo de imagem não permitido.']];
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $nomeFinal = bin2hex(random_bytes(16)) . '.' . $extensao;
    $destino = ROOT_DIR . '/uploads/' . $nomeFinal;

    $dirUploads = ROOT_DIR . '/uploads';
    if (!is_dir($dirUploads)) {
        mkdir($dirUploads, 0755, true);
    }

    if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return ['sucesso' => true, 'nome_final' => $nomeFinal];
    }

    return ['sucesso' => false, 'erros' => ['Falha ao salvar imagem.']];
}

function resumirTexto(string $texto, int $limite = 200): string {
    $texto = strip_tags($texto);
    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }
    return mb_substr($texto, 0, $limite) . '...';
}

function formatarData(string $data): string {
    $timestamp = strtotime($data);
    return date('d/m/Y \à\s H:i', $timestamp);
}

function urlCapa(?string $capa): string {
    if ($capa && file_exists(ROOT_DIR . '/uploads/' . $capa)) {
        return '/uploads/' . $capa;
    }
    return ''; // sem imagem
}
```

### `src/upload.php`

```php
<?php
// src/upload.php — placeholder (a lógica está em functions.php)
// Este arquivo existe para organização, mas o upload é tratado em functions.php:uploadImagemCapa()
```

### `templates/layout.php`

```php
<?php
// templates/layout.php
function layout(string $titulo, string $conteudo, bool $logado = false): string {
    $adminLink = $logado
        ? '<a href="/admin">Painel</a> | <a href="/admin/logout" onclick="document.getElementById(\'logout-form\').submit();return false;">Sair</a>'
        : '<a href="/admin/login">Admin</a>';

    $csrfToken = h($_SESSION['csrf_token'] ?? '');

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titulo} — Blog PHP</title>
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
        {$conteudo}
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
HTML;
}
```

### `templates/home.php`

```php
<?php
// templates/home.php (já com $posts, $pagina, $totalPaginas)

$conteudo = '';
$logado = Session::estaLogado();

// Mensagem flash
$sucesso = Session::flash('sucesso');
if ($sucesso) {
    $conteudo .= '<div class="alert alert-sucesso">' . h($sucesso) . '</div>';
}

if (empty($posts)) {
    $conteudo .= '<div class="vazio"><h2>Nenhum post ainda.</h2><p>O administrador ainda não publicou conteúdo.</p></div>';
} else {
    foreach ($posts as $post) {
        $capaUrl = urlCapa($post['capa']);
        $capaHtml = $capaUrl ? '<img src="' . h($capaUrl) . '" alt="' . h($post['titulo']) . '" class="capa">' : '';
        $resumo = h($post['resumo'] ?: resumirTexto($post['conteudo']));

        $conteudo .= <<<POST
        <article class="post-card">
            {$capaHtml}
            <div class="corpo">
                <h2><a href="/post?id={$post['id']}">{$post['titulo']}</a></h2>
                <div class="meta">Publicado em {$post['criado_em']}</div>
                <p class="resumo">{$resumo}</p>
                <a href="/post?id={$post['id']}" style="color:#6366f1;font-weight:600;text-decoration:none;font-size:0.9rem;margin-top:8px;display:inline-block;">Ler mais →</a>
            </div>
        </article>
        POST;
    }

    // Paginação
    if ($totalPaginas > 1) {
        $conteudo .= '<div class="paginacao">';
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $classe = ($i === $pagina) ? 'ativo' : '';
            $conteudo .= "<a href='/?pagina={$i}' class='{$classe}'>{$i}</a>";
        }
        $conteudo .= '</div>';
    }
}

echo layout('Home', $conteudo, $logado);
```

### `templates/post.php`

```php
<?php
// templates/post.php (já com $post definido)
$logado = Session::estaLogado();
$capaUrl = urlCapa($post['capa']);
$capaHtml = $capaUrl ? "<img src='" . h($capaUrl) . "' alt='" . h($post['titulo']) . "' class='capa-full'>" : '';
$data = formatarData($post['criado_em']);
$conteudoPost = nl2br(h($post['conteudo']));

$adminBotoes = '';
if ($logado) {
    $csrfToken = $_SESSION['csrf_token'];
    $adminBotoes = <<<BTN
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

$conteudo = <<<HTML
<article>
    <div class="card">
        <a href="/" style="color:#6366f1;text-decoration:none;font-size:0.9rem;">← Voltar</a>

        <div class="post-header">
            <h1>{$post['titulo']}</h1>
            <div class="meta">Publicado em {$data}</div>
            {$capaHtml}
        </div>

        <div class="post-content">
            {$conteudoPost}
        </div>

        {$adminBotoes}
    </div>
</article>
HTML;

echo layout('Post: ' . $post['titulo'], $conteudo, $logado);
```

### `templates/criar.php`

```php
<?php
// templates/criar.php
$dados = $dados ?? [];
$erros = $erros ?? [];
$logado = Session::estaLogado();

$erroHtml = '';
if (!empty($erros)) {
    $erroHtml = '<div class="alert alert-erro"><ul>';
    foreach ($erros as $erro) {
        $erroHtml .= '<li>' . h($erro) . '</li>';
    }
    $erroHtml .= '</ul></div>';
}

$csrfToken = $_SESSION['csrf_token'];
$titulo = h($dados['titulo'] ?? '');
$resumo = h($dados['resumo'] ?? '');
$conteudoPost = h($dados['conteudo'] ?? '');

$html = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Novo Post</h1>
        <a href="/admin" class="btn btn-secondary btn-sm">← Voltar</a>
    </div>
    {$erroHtml}
    <form method="post" action="/admin/criar" enctype="multipart/form-data">
        {$csrfToken}
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="{$titulo}" required maxlength="200">
        </div>
        <div class="form-group">
            <label for="resumo">Resumo (opcional)</label>
            <input type="text" id="resumo" name="resumo" value="{$resumo}" maxlength="500">
        </div>
        <div class="form-group">
            <label for="capa">Imagem de Capa (opcional)</label>
            <input type="file" id="capa" name="capa" accept="image/*">
        </div>
        <div class="form-group">
            <label for="conteudo">Conteúdo</label>
            <textarea id="conteudo" name="conteudo" required>{$conteudoPost}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Publicar Post</button>
    </form>
</div>
HTML;

echo layout('Novo Post', $html, $logado);
```

### `templates/editar.php`

```php
<?php
// templates/editar.php
$erros = $erros ?? [];
$logado = Session::estaLogado();

$erroHtml = '';
if (!empty($erros)) {
    $erroHtml = '<div class="alert alert-erro"><ul>';
    foreach ($erros as $erro) {
        $erroHtml .= '<li>' . h($erro) . '</li>';
    }
    $erroHtml .= '</ul></div>';
}

$csrfToken = $_SESSION['csrf_token'];
$titulo = h($dados['titulo'] ?? $post['titulo'] ?? '');
$resumo = h($dados['resumo'] ?? $post['resumo'] ?? '');
$conteudoPost = h($dados['conteudo'] ?? $post['conteudo'] ?? '');
$capaAtual = urlCapa($post['capa'] ?? null);
$capaInfo = $capaAtual ? "<p style='font-size:0.85rem;color:#64748b;margin-top:4px'>Capa atual: " . h($post['capa']) . "</p>" : '';

$html = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Editar Post</h1>
        <a href="/post?id={$post['id']}" class="btn btn-secondary btn-sm">← Cancelar</a>
    </div>
    {$erroHtml}
    <form method="post" action="/admin/editar" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <input type="hidden" name="id" value="{$post['id']}">
        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="{$titulo}" required maxlength="200">
        </div>
        <div class="form-group">
            <label for="resumo">Resumo (opcional)</label>
            <input type="text" id="resumo" name="resumo" value="{$resumo}" maxlength="500">
        </div>
        <div class="form-group">
            <label for="capa">Imagem de Capa (substituir)</label>
            <input type="file" id="capa" name="capa" accept="image/*">
            {$capaInfo}
        </div>
        <div class="form-group">
            <label for="conteudo">Conteúdo</label>
            <textarea id="conteudo" name="conteudo" required>{$conteudoPost}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
HTML;

echo layout('Editar Post', $html, $logado);
```

### `templates/login.php`

```php
<?php
// templates/login.php
$erro = Session::flash('erro');
$emailVal = h(Session::flash('email', null) ?? '');
$csrfToken = $_SESSION['csrf_token'];

$erroHtml = $erro ? '<div class="alert alert-erro">' . h($erro) . '</div>' : '';

$conteudo = <<<HTML
<div class="card" style="max-width:420px;margin:40px auto;">
    <h1>Login — Administração</h1>
    {$erroHtml}
    <form method="post" action="/admin/login">
        <input type="hidden" name="csrf_token" value="{$csrfToken}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{$emailVal}" required autofocus>
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:0.85rem;color:#94a3b8;">
        Admin padrão: admin@blog.com / admin123
    </p>
</div>
HTML;

echo layout('Login Admin', $conteudo, Session::estaLogado());
```

### `templates/admin.php` (Painel)

```php
<?php
// templates/admin.php (já com $posts)
$logado = Session::estaLogado();
$csrfToken = $_SESSION['csrf_token'];

$sucesso = Session::flash('sucesso');
$flashHtml = $sucesso ? '<div class="alert alert-sucesso">' . h($sucesso) . '</div>' : '';

$linhas = '';
foreach ($posts as $post) {
    $data = formatarData($post['criado_em']);
    $titulo = h($post['titulo']);
    $linhas .= <<<ROW
    <tr>
        <td>{$post['id']}</td>
        <td><a href="/post?id={$post['id']}" style="color:#1e293b;text-decoration:none;">{$titulo}</a></td>
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

$conteudo = <<<HTML
<div class="card">
    <div class="flex-between mb-16">
        <h1>Painel de Administração</h1>
        <a href="/admin/criar" class="btn btn-primary">+ Novo Post</a>
    </div>
    {$flashHtml}
    <table class="tabela">
        <thead>
            <tr><th>ID</th><th>Título</th><th>Data</th><th>Ações</th></tr>
        </thead>
        <tbody>
            {$linhas}
        </tbody>
    </table>
    <p style="text-align:center;color:#94a3b8;margin-top:16px;">
        Total de posts: {count($posts)}
    </p>
</div>
HTML;

echo layout('Painel Admin', $conteudo, $logado);
```

---

## Como Executar

```bash
# 1. Acesse o diretório public/
cd /caminho/para/projetos/blog/public

# 2. Inicie o servidor PHP
php -S localhost:8080

# 3. Acesse http://localhost:8080
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
- **Roteador manual** com suporte a parâmetros dinâmicos
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
