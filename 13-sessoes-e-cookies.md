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
$_SESSION['usuario_id'] = 42;
$_SESSION['nome'] = 'João Silva';
$_SESSION['logado_em'] = time();

echo "Sessão iniciada para {$_SESSION['nome']}";
?>
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
    'nome'  => 'João',
    'email' => 'joao@email.com',
    'role'  => 'admin',
];

// Guardar preferências
$_SESSION['tema'] = 'dark';
$_SESSION['carrinho'] = [
    ['produto_id' => 10, 'quantidade' => 2],
    ['produto_id' => 15, 'quantidade' => 1],
];

// Recuperando dados
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    echo "Bem-vindo, {$usuario['nome']}!<br>\n";
    echo "Função: {$usuario['role']}<br>\n";
}

// Operações com session
$totalItens = count($_SESSION['carrinho']);
echo "Itens no carrinho: {$totalItens}<br>\n";

// Remover um item específico
unset($_SESSION['carrinho'][0]);

// Adicionar ao carrinho
$_SESSION['carrinho'][] = ['produto_id' => 20, 'quantidade' => 3];
```

### Verificar se sessão está ativa

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ou
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Sessão ativa<br>\n";
}
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
echo "Sessão destruída.<br>\n";
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
header('Location: /login.php');
exit;
```

---

## 4. `session_regenerate_id()`

Regenere o ID da sessão após o login para prevenir **session fixation**.

```php
<?php
session_start();

// Após login bem-sucedido
$_SESSION['usuario_id'] = $usuario['id'];

// Regenera o ID — importante para segurança!
// true: remove o arquivo de sessão antigo
session_regenerate_id(true);

echo "Login realizado. ID da sessão foi regenerado.<br>\n";
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

session_start();
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

session_start();
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
// setcookie(nome, valor, expira, path, domain, secure, httponly, samesite)

// Cookie simples
setcookie('tema', 'dark');

// Cookie com tempo de expiração
// time() + segundos
setcookie('lembrar_login', 'sim', time() + (86400 * 30)); // 30 dias
setcookie('preferencia_idioma', 'pt-BR', time() + (86400 * 365)); // 1 ano

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
    ]
);
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
    ]
);
```

### `setrawcookie()` — Cookie sem URL-encode

```php
<?php
// setcookie aplica urlencode
setcookie('nome', 'João Silva'); // cookie armazenado como: Jo%C3%A3o+Silva

// setrawcookie NÃO aplica urlencode (você é responsável)
setrawcookie('token', rawurlencode('abcd/xyz='));
```

---

## 7. `$_COOKIE` — Lendo Cookies

```php
<?php
// Cookies definidos com setcookie só estarão disponíveis
// em $_COOKIE na PRÓXIMA requisição

// Leitura segura com operador null coalescing
$tema = $_COOKIE['tema'] ?? 'light';
$idioma = $_COOKIE['preferencia_idioma'] ?? 'pt-BR';

// Verificar existência
if (isset($_COOKIE['lembrar_login'])) {
    echo "Usuário escolheu 'lembrar login'.<br>\n";
}

// Listar todos os cookies recebidos
echo "<h3>Cookies recebidos:</h3>\n";
echo "<ul>\n";
foreach ($_COOKIE as $nome => $valor) {
    echo "<li>" . htmlspecialchars($nome) . " = " . htmlspecialchars($valor) . "</li>\n";
}
echo "</ul>\n";
```

### Remover um Cookie

```php
<?php
// Para remover, defina com tempo de expiração no passado
setcookie('tema', '', time() - 3600);
setcookie('preferencia_idioma', '', time() - 3600, '/');

// Com opções de array
setcookie('lembrar_login', '', [
    'expires' => time() - 3600,
    'path'    => '/',
    'secure'  => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
```

---

## 8. Cookie de Sessão vs Cookie Persistente

```php
<?php
// Cookie de sessão: definido SEM expires ou com lifetime 0
// Desaparece quando o navegador fecha
setcookie('visitou_pagina', '1', 0);
setcookie('visitou_pagina', '1', ['expires' => 0]);

// Cookie persistente: tem tempo de expiração definido
// Sobrevive ao fechamento do navegador
setcookie('lembrar_usuario', 'joao', time() + (86400 * 30)); // 30 dias
```

---

## 9. Flash Messages com Sessão

Mensagens "flash" são exibidas apenas uma vez e depois removidas. Ideais para feedback pós-redirecionamento.

```php
<?php
session_start();

// flash.php — Funções para flash messages

function flash(string $chave, string $mensagem = null): ?string {
    if ($mensagem !== null) {
        // SET: guarda a mensagem
        $_SESSION['flash'][$chave] = $mensagem;
        return null;
    }

    // GET: recupera e remove
    $msg = $_SESSION['flash'][$chave] ?? null;
    unset($_SESSION['flash'][$chave]);
    return $msg;
}

function flashSucesso(string $mensagem): void {
    flash('sucesso', $mensagem);
}

function flashErro(string $mensagem): void {
    flash('erro', $mensagem);
}

function flashInfo(string $mensagem): void {
    flash('info', $mensagem);
}

// Uso:

// Em salvar.php (após processar formulário)
flashSucesso('Registro salvo com sucesso!');
header('Location: /listar.php');
exit;

// Em listar.php (na view)
$sucesso = flash('sucesso');
if ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<?php
$erro = flash('erro');
if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
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

    public static function set(string $tipo, string $mensagem): void {
        self::init();
        $_SESSION[self::KEY][$tipo] = $mensagem;
    }

    public static function get(string $tipo): ?string {
        self::init();
        $msg = $_SESSION[self::KEY][$tipo] ?? null;
        unset($_SESSION[self::KEY][$tipo]);
        return $msg;
    }

    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error', $msg); }
    public static function warning(string $msg): void { self::set('warning', $msg); }
    public static function info(string $msg): void    { self::set('info', $msg); }

    public static function all(): array {
        self::init();
        $mensagens = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];
        return $mensagens;
    }

    public static function render(): string {
        $html = '';
        foreach (self::all() as $tipo => $msg) {
            $html .= sprintf(
                '<div class="flash flash-%s">%s</div>',
                htmlspecialchars($tipo),
                htmlspecialchars($msg)
            );
        }
        return $html;
    }
}

// Uso
FlashMessages::success('Arquivo enviado!');
FlashMessages::error('Falha na conexão.');
echo FlashMessages::render();
```

---

## 10. Login Básico com Sessão

```php
<?php
// login.php
session_start();

$erro = '';
$email = '';

// Usuários hardcoded para demonstração
$usuarios = [
    'admin@email.com' => [
        'nome'  => 'Administrador',
        'senha' => password_hash('admin123', PASSWORD_DEFAULT),
        'id'    => 1,
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (isset($usuarios[$email])) {
        $usuario = $usuarios[$email];

        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido!
            $_SESSION['usuario'] = [
                'id'    => $usuario['id'],
                'nome'  => $usuario['nome'],
                'email' => $email,
            ];

            session_regenerate_id(true); // previne session fixation

            header('Location: /dashboard.php');
            exit;
        } else {
            $erro = 'Email ou senha incorretos.';
        }
    } else {
        // Use mensagem genérica para não revelar se o email existe
        $erro = 'Email ou senha incorretos.';
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
    <?php if ($erro): ?>
        <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
```

### Página protegida (dashboard)

```php
<?php
// dashboard.php
session_start();

// Verifica se está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: /login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</h1>
    <p>Email: <?= htmlspecialchars($usuario['email']) ?></p>
    <p>ID: <?= $usuario['id'] ?></p>
    <a href="/logout.php">Sair</a>
</body>
</html>
```

### Função auxiliar para proteger páginas

```php
<?php
function requerLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['url_redirecionar'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

// Em qualquer página protegida:
requerLogin();
// O usuário está logado, continue...
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
session_regenerate_id(true);
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
    header('Location: /login.php?erro=sessao');
    exit;
}

// 2. Cookies HttpOnly (não acessível via JavaScript)
ini_set('session.cookie_httponly', '1');

// 3. Cookies Secure (apenas HTTPS)
ini_set('session.cookie_secure', '1');

// 4. SameSite=Strict para prevenir CSRF
ini_set('session.cookie_samesite', 'Strict');
```

---

## 12. Exemplo Prático: Carrinho de Compras com Sessão

```php
<?php
// carrinho.php
session_start();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Produtos de exemplo
$produtos = [
    1 => ['nome' => 'Camiseta PHP',         'preco' => 59.90],
    2 => ['nome' => 'Caneca Programador',   'preco' => 39.90],
    3 => ['nome' => 'Adesivo Elefante PHP', 'preco' =>  9.90],
    4 => ['nome' => 'Livro PHP Moderno',    'preco' => 129.90],
];

// Ação: adicionar
if (isset($_GET['adicionar'])) {
    $id = (int) $_GET['adicionar'];
    if (isset($produtos[$id])) {
        if (isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id]['qtd']++;
        } else {
            $_SESSION['carrinho'][$id] = [
                'nome'  => $produtos[$id]['nome'],
                'preco' => $produtos[$id]['preco'],
                'qtd'   => 1,
            ];
        }
        $_SESSION['flash'] = "{$produtos[$id]['nome']} adicionado ao carrinho!";
    }
}

// Ação: remover
if (isset($_GET['remover'])) {
    $id = (int) $_GET['remover'];
    unset($_SESSION['carrinho'][$id]);
}

// Ação: limpar carrinho
if (isset($_GET['limpar'])) {
    $_SESSION['carrinho'] = [];
}

// Calcular total
$total = 0;
$contador = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total += $item['preco'] * $item['qtd'];
    $contador += $item['qtd'];
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
    .produtos { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .produto { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
    .total { font-size: 1.2rem; font-weight: bold; text-align: right; }
</style></head>
<body>
    <h1>Carrinho (<?= $contador ?> itens)</h1>

    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <h2>Produtos</h2>
    <div class="produtos">
        <?php foreach ($produtos as $id => $p): ?>
            <div class="produto">
                <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                R$ <?= number_format($p['preco'], 2, ',', '.') ?><br>
                <a href="?adicionar=<?= $id ?>" class="btn btn-add">Adicionar</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['carrinho'])): ?>
        <h2>Seu Carrinho</h2>
        <table>
            <tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr>
            <?php foreach ($_SESSION['carrinho'] as $id => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nome']) ?></td>
                <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                <td><?= $item['qtd'] ?></td>
                <td>R$ <?= number_format($item['preco'] * $item['qtd'], 2, ',', '.') ?></td>
                <td><a href="?remover=<?= $id ?>" class="btn btn-remove">X</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: R$ <?= number_format($total, 2, ',', '.') ?></p>
        <a href="?limpar=1" class="btn btn-clear">Limpar Carrinho</a>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</body>
</html>
```

---

## 13. Cookies com Arrays (Serialização)

```php
<?php
// Cookies armazenam strings. Para guardar arrays, serialize ou json_encode.

// Guardar preferências como JSON
$preferencias = ['tema' => 'dark', 'fonte' => 'grande', 'notificacoes' => false];
setcookie('prefs', json_encode($preferencias), time() + (86400 * 365), '/');

// Recuperar
$prefs = json_decode($_COOKIE['prefs'] ?? '{}', true);
echo "Tema: " . ($prefs['tema'] ?? 'light') . "<br>\n";

// Contador de visitas com cookie
$visitas = (int) ($_COOKIE['visitas'] ?? 0);
$visitas++;
setcookie('visitas', (string) $visitas, time() + (86400 * 365), '/');
echo "Você visitou esta página {$visitas} vez(es).<br>\n";
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
