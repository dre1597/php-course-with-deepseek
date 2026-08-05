# Módulo 13: Sessões e Cookies

## Visão Geral

HTTP é um protocolo **stateless** (sem estado). Cada requisição é independente. Sessões e cookies permitem que o servidor reconheça usuários entre requisições. Sessões armazenam dados no servidor; cookies armazenam dados no navegador do cliente.

---

## 1. Sessões: `session_start()`

`session_start()` deve ser chamada **antes de qualquer output** HTML, echo, print ou mesmo espaços em branco fora de `<?php ?>`.

```php
<?php
// SEMPRE no topo do arquivo, antes de qualquer HTML
session_start();

// Agora $_SESSION está disponível
$_SESSION['user_id'] = 42;
$_SESSION['name'] = 'João Silva';
$_SESSION['logged_in_at'] = time();

echo "Sessão iniciada para {$_SESSION['name'
```

> ⚠️ **Cuidado:** Se houver qualquer output antes de `session_start()`, o PHP emitirá: `Warning: session_start(): Cannot start session when headers already sent`.

---

## 2. `$_SESSION`: Guardar e Recuperar Dados

```php
<?php
session_start();

// Guardando dados
$_SESSION['usuario'] = [
    'id'    => 1,
    'name'  => 'João',
    'email' => 'joao@email.com',
    'role'  => 'admin',
];

// Guardar preferências
$_SESSION['theme'] = 'dark';
$_SESSION['cart'] = [
    ['product_id' => 10, 'quantity' => 2],
    ['product_id' => 15, 'quantity' => 1],
];

// Recuperando dados
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    echo "Bem-vindo, {$user['name']}!<br>\n";
    echo "Função: {$user['role']}<br>\n";
}

// Operações com session
$totalItems = count($_SESSION['cart']);

// Remover um item específico
unset($_SESSION['cart'][0]);

// Adicionar ao carrinho
$_SESSION['cart'][] = ['product_id' => 20, 'quantity'
```

### Verificar se sessão está ativa

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ou
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Sessão ativa<br
```

---

## 3. `session_unset()` e `session_destroy()`

```php
<?php
session_start();

// session_unset() — limpa todas as variáveis da sessão
// mas mantém a sessão ativa
session_unset();
echo "Variáveis limpas. A sessão continua ativa.<br>\n";

// session_destroy() — destrói a sessão do servidor
// O cookie de sessão ainda existe no navegador!
$_SESSION = []; // limpa o array

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // expira no passado
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
echo "Sessão destruída.<
```

### Logout completo (receita)

```php
<?php
// logout.php
session_start();

// 1. Limpa os dados da sessão
$_SESSION = [];

// 2. Remove o cookie de sessão
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

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Redireciona para a página de login
header('Location: /login.php')
```

---

## 4. `session_regenerate_id()`

Regenere o ID da sessão após o login para prevenir **session fixation**.

```php
<?php
session_start();

// Após login bem-sucedido
$_SESSION['user_id'] = $user['id'];

// Regenera o ID — importante para segurança!
// true: remove o arquivo de sessão antigo
session_regenerate_id(true);

echo "Login realizado. ID da sessão foi regenerado.<
```

> 💡 **Dica:** Sempre chame `session_regenerate_id(true)` após login, logout e mudanças de permissão do usuário.

---

## 5. Configuração de Sessão

```php
<?php
// Configurar antes de session_start()

// Tempo de vida da sessão no servidor (em segundos)
// 3600 = 1 hora, 86400 = 24 horas
ini_set('session.gc_maxlifetime', 86400); // 24 horas

// Cookies de sessão
ini_set('session.cookie_lifetime', 0);      // 0 = até fechar navegador
ini_set('session.cookie_path', '/');         // disponível em todo site
ini_set('session.cookie_domain', '');        // domínio atual
ini_set('session.cookie_secure', '1');       // apenas HTTPS
ini_set('session.cookie_httponly', '1');     // inacessível via JavaScript
ini_set('session.cookie_samesite', 'Lax');   // proteção CSRF

// Nome do cookie de sessão (mudar do padrão PHPSESSID)
session_name('MEUAPP_SESSID');

// Diretório onde os arquivos de sessão são salvos
// session.save_path — não pode ser alterado via ini_set em produção
// Configurar no php.ini

// Probabilidade de coleta de lixo (garbage collection)
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100); // 1% de chance a cada requisição

session_s
```

### Configuração via `session_set_cookie_params()`

```php
<?php
// Alternativa mais limpa que ini_set
session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'domain'   => 'meusite.com.br',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_s
```

### Configuração típica no `php.ini`

```ini
session.save_handler = files
session.save_path = "/tmp"
session.gc_maxlifetime = 1440          ; 24 minutos (padrão)
session.cookie_lifetime = 0            ; expira ao fechar navegador
session.cookie_httponly = On           ; não acessível via JS
session.cookie_secure = On             ; apenas HTTPS
session.cookie_samesite = "Lax"        ; CSRF
session.use_strict_mode = On           ; rejeita IDs não inicializados
session.use_only_cookies = On          ; não permite ID na URL
```

---

## 6. Cookies: `setcookie()`

Cookies armazenam dados no navegador do cliente. São enviados em toda requisição HTTP subsequente ao mesmo domínio.

```php
<?php
// setcookie(name, valor, expira, path, domain, secure, httponly, samesite)

// Cookie simples
setcookie('theme', 'dark');

// Cookie com tempo de expiração
// time() + segundos
setcookie('remember_login', 'sim', time() + (86400 * 30)); // 30 dias
setcookie('locale', 'pt-BR', time() + (86400 * 365)); // 1 ano

// Cookie com caminho específico (só disponível em /admin)
setcookie('admin_token', 'abc123', time() + 3600, '/admin');

// Cookie com todas as opções de segurança
setcookie(
    'token',
    'valor-codificado',
    [
        'expires'  => time() + 86400,
        'path'     => '/',
        'domain'   => '',               // domínio atual
        'secure'   => true,             // apenas HTTPS
        'httponly' => true,             // não acessível via JavaScript
        'samesite' => 'Strict',          // Lax, Strict ou None
 
```

> **PHP 8.5+** — Nova flag `partitioned`

```php
<?php
// PHP 8.5+: Cookies Particionados (CHIPS — Cookies Having Independent Partitioned State)
// Útil para cookies em iframes de terceiros
// https://developer.chrome.com/docs/privacy-sandbox/chips/
setcookie(
    'widget_pref',
    'dark',
    [
        'expires'      => time() + 86400 * 30,
        'path'         => '/',
        'secure'       => true,
        'httponly'     => true,
        'samesite'     => 'None',       // Requer None para cross-site
        'partitioned'  => true,          // PHP 8.5+ NOVIDADE!
 
```

### `setrawcookie()` — Cookie sem URL-encode

```php
<?php
// setcookie aplica urlencode
setcookie('name', 'João Silva'); // cookie armazenado como: Jo%C3%A3o+Silva

// setrawcookie NÃO aplica urlencode (você é responsável)
setrawcookie('token', rawurlencode('abcd/x
```

---

## 7. `$_COOKIE` — Lendo Cookies

```php
<?php
// Cookies definidos com setcookie só estarão disponíveis
// em $_COOKIE na PRÓXIMA requisição

// Leitura segura com operador null coalescing
$theme = $_COOKIE['theme'] ?? 'light';
$locale = $_COOKIE['locale'] ?? 'pt-BR';

// Verificar existência
if (isset($_COOKIE['remember_login'])) {
    echo "Usuário escolheu 'lembrar login'.<br>\n";
}

// Listar todos os cookies recebidos
echo "<h3>Cookies recebidos:</h3>\n";
echo "<ul>\n";
foreach ($_COOKIE as $name => $value) {
    echo "<li>" . htmlspecialchars($name) . " = " . htmlspecialchars($value) . "</li>\n";
}
echo "</
```

### Remover um Cookie

```php
<?php
// Para remover, defina com tempo de expiração no passado
setcookie('theme', '', time() - 3600);
setcookie('locale', '', time() - 3600, '/');

// Com opções de array
setcookie('remember_login', '', [
    'expires' => time() - 3600,
    'path'    => '/',
    'secure'  => true,
    'httponly' => true,
    'samesite' => 'Stric
```

---

## 8. Cookie de Sessão vs Cookie Persistente

```php
<?php
// Cookie de sessão: definido SEM expires ou com lifetime 0
// Desaparece quando o navegador fecha
setcookie('visited_page', '1', 0);
setcookie('visited_page', '1', ['expires' => 0]);

// Cookie persistente: tem tempo de expiração definido
// Sobrevive ao fechamento do navegador
setcookie('remember_user', 'joao', time() + (86400 * 30)); // 
```

---

## 9. Flash Messages com Sessão

Mensagens "flash" são exibidas apenas uma vez e depois removidas. Ideais para feedback pós-redirecionamento.

```php
<?php
session_start();

// flash.php — Funções para flash messages

function flash(string $key, string $message = null): ?string {
    if ($message !== null) {
        // SET: guarda a mensagem
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    // GET: recupera e remove
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flashSucesso(string $message): void {
    flash('success', $message);
}

function flashErro(string $message): void {
    flash('error', $message);
}

function flashInfo(string $message): void {
    flash('info', $message);
}

// Uso:

// Em salvar.php (após process formulário)
flashSucesso('Registro salvo com sucesso!');
header('Location: /list.php');
exit;

// Em list.php (na view)
$success = flash('success');
if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php
$error = flash('error');
if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php en
```

### Classe Flash Messages completa

```php
<?php
class FlashMessages {
    private const KEY = '_flash_messages';

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    public static function set(string $type, string $message): void {
        self::init();
        $_SESSION[self::KEY][$type] = $message;
    }

    public static function get(string $type): ?string {
        self::init();
        $msg = $_SESSION[self::KEY][$type] ?? null;
        unset($_SESSION[self::KEY][$type]);
        return $msg;
    }

    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error', $msg); }
    public static function warning(string $msg): void { self::set('warning', $msg); }
    public static function info(string $msg): void    { self::set('info', $msg); }

    public static function all(): array {
        self::init();
        $messages = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];
        return $messages;
    }

    public static function render(): string {
        $html = '';
        foreach (self::all() as $type => $msg) {
            $html .= sprintf(
                '<div class="flash flash-%s">%s</div>',
                htmlspecialchars($type),
                htmlspecialchars($msg)
            );
        }
        return $html;
    }
}

// Uso
FlashMessages::success('Arquivo enviado!');
FlashMessages::error('Falha na conexão.');
echo FlashMessages::re
```

---

## 10. Login Básico com Sessão

```php
<?php
// login.php
session_start();

$error = '';
$email = '';

// Usuários hardcoded para demonstração
$users = [
    'admin@email.com' => [
        'name'  => 'Administrador',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'id'    => 1,
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Preencha todos os campos.';
    } elseif (isset($users[$email])) {
        $user = $users[$email];

        if (password_verify($password, $user['password'])) {
            // Login bem-sucedido!
$_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $email,
            ];

            session_regenerate_id(true); // previne session fixation

            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Email ou password incorretos.';
        }
    } else {
        // Use mensagem genérica para não revelar se o email existe
        $error = 'Email ou password incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Login</title>
<style>
    body { font-family: sans-serif; max-width: 380px; margin: 60px auto; }
    .erro { background: #fee; color: #c00; padding: 10px; border-radius: 4px; }
    label { display: block; margin: 12px 0 4px; font-weight: 600; }
    input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { margin-top: 16px; padding: 10px 24px; background: #2563eb; color: white;
             border: none; border-radius: 4px; cursor: pointer; }
</style></head>
<body>
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="erro"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>
</body>
```

### Página protegida (dashboard)

```php
<?php
// dashboard.php
session_start();

// Verifica se está logado
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($user['name']) ?>!</h1>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>ID: <?= $user['id'] ?></p>
    <a href="/logout.php">Sair</a>
</body>
```

### Função auxiliar para proteger páginas

```php
<?php
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

// Em qualquer página protegida:
requireLogin();
// O usuário está logado, cont
```

---

## 11. Segurança: Session Fixation e Session Hijacking

### Session Fixation

O atacante define um ID de sessão conhecido (ex: via URL `?PHPSESSID=123`) e induz a vítima a usá-lo. Após o login, o atacante usa o mesmo ID para acessar a sessão autenticada.

**Mitigação:**
```php
<?php
session_start();

// 1. Habilitar strict mode (php.ini)
// session.use_strict_mode = On

// 2. Regenerar ID após login
session_regenerate_id
```

### Session Hijacking

O atacante rouba o ID de sessão da vítima (ex: via XSS, sniffing de rede).

**Mitigações:**
```php
<?php
// 1. Vincular sessão ao IP e User-Agent
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Sessão potencialmente roubada — força logout
    session_destroy();
    header('Location: /login.php?error=session');
    exit;
}

// 2. Cookies HttpOnly (não acessível via JavaScript)
ini_set('session.cookie_httponly', '1');

// 3. Cookies Secure (apenas HTTPS)
ini_set('session.cookie_secure', '1');

// 4. SameSite=Strict para prevenir CSRF
ini_set('session.cookie_samesite', 'St
```

---

## 12. Exemplo Prático: Carrinho de Compras com Sessão

```php
<?php
// carrinho.php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Products de exemplo
$products = [
    1 => ['name' => 'Camiseta PHP',         'price' => 59.90],
    2 => ['name' => 'Caneca Programador',   'price' => 39.90],
    3 => ['name' => 'Adesivo Elefante PHP', 'price' =>  9.90],
    4 => ['name' => 'Livro PHP Moderno',    'price' => 129.90],
];

// Ação: adicionar
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if (isset($products[$id])) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name'  => $products[$id]['name'],
                'price' => $products[$id]['price'],
                'qty'   => 1,
            ];
        }
        $_SESSION['flash'] = "{$products[$id]['name']} adicionado ao carrinho!";
    }
}

// Ação: remover
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

// Ação: limpar carrinho
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

// Calcular total
$total = 0;
$counter = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
    $counter += $item['qty'];
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Carrinho de Compras</title>
<style>
    body { font-family: sans-serif; max-width: 700px; margin: 30px auto; padding: 0 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    .flash { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
    .btn { display: inline-block; padding: 6px 12px; text-decoration: none; border-radius: 4px;
           color: white; font-size: 0.85rem; }
    .btn-add { background: #2563eb; }
    .btn-remove { background: #dc2626; }
    .btn-clear { background: #6b7280; }
    .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .produto { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
    .total { font-size: 1.2rem; font-weight: bold; text-align: right; }
</style></head>
<body>
    <h1>Carrinho (<?= $counter ?> itens)</h1>

    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <h2>Products</h2>
    <div class="products">
        <?php foreach ($products as $id => $p): ?>
            <div class="produto">
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                R$ <?= number_format($p['price'], 2, ',', '.') ?><br>
                <a href="?add=<?= $id ?>" class="btn btn-add">Adicionar</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
        <h2>Seu Carrinho</h2>
        <table>
            <tr><th>Product</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr>
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                <td><?= $item['qty'] ?></td>
                <td>R$ <?= number_format($item['price'] * $item['qty'], 2, ',', '.') ?></td>
                <td><a href="?remove=<?= $id ?>" class="btn btn-remove">X</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: R$ <?= number_format($total, 2, ',', '.') ?></p>
        <a href="?clear=1" class="btn btn-clear">Limpar Carrinho</a>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</body>
```

---

## 13. Cookies com Arrays (Serialização)

```php
<?php
// Cookies armazenam strings. Para guardar arrays, serialize ou json_encode.

// Guardar preferências como JSON
$preferences = ['theme' => 'dark', 'font' => 'large', 'notifications' => false];
setcookie('prefs', json_encode($preferences), time() + (86400 * 365), '/');

$prefs = json_decode($_COOKIE['prefs'] ?? '{}', true);
echo "Tema: " . ($prefs['theme'] ?? 'light') . "<br>\n";

$visits = (int) ($_COOKIE['visits'] ?? 0);
$visits++;
setcookie('visits', (string) $visits, time() + (86400 * 365), '/');
echo "Você visitou esta página {$visits} vez(es).<
```

> ⚠️ **Cuidado:** Cookies têm limite de ~4KB por cookie e ~50 cookies por domínio. Não armazene dados grandes em cookies.

---

## 📚 Referências

- [PHP: Sessões](https://www.php.net/manual/pt_BR/book.session.php)
- [PHP: session_start](https://www.php.net/manual/pt_BR/function.session-start.php)
- [PHP: setcookie](https://www.php.net/manual/pt_BR/function.setcookie.php)
- [PHP: session_set_cookie_params](https://www.php.net/manual/pt_BR/function.session-set-cookie-params.php)
- [PHP: session_regenerate_id](https://www.php.net/manual/pt_BR/function.session-regenerate-id.php)
- [OWASP: Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [MDN: Cookies HTTP](https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Cookies)
- [MDN: Set-Cookie SameSite](https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
