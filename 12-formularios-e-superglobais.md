# Módulo 12: Formulários e Superglobais

## Visão Geral

Formulários são a principal forma de interação do usuário com aplicações web. PHP oferece superglobais como `$_GET`, `$_POST`, `$_REQUEST` para capturar dados enviados pelo cliente. Neste módulo, você aprenderá a criar formulários, validar dados, sanitizar entradas e proteger sua aplicação.

---

## 1. Superglobais: Visão Geral

PHP disponibiliza variáveis **superglobais** — acessíveis em qualquer escopo (funções, classes, escopo global) sem necessidade de `global $variavel`.

| Superglobal | Descrição |
|-------------|-----------|
| `$_GET`     | Parâmetros via query string da URL |
| `$_POST`    | Dados enviados via método POST (corpo da requisição) |
| `$_REQUEST` | Combinação de `$_GET`, `$_POST` e `$_COOKIE` (ordem definida em `request_order`) |
| `$_SERVER`  | Informações do servidor e da requisição HTTP |
| `$_ENV`     | Variáveis de ambiente |
| `$_FILES`   | Arquivos enviados via upload |
| `$_COOKIE`  | Cookies enviados pelo cliente |
| `$_SESSION` | Dados da sessão (requer `session_start()`) |
| `$GLOBALS`  | Todas as variáveis do escopo global |

---

## 2. `$_GET` — Parâmetros na URL

```php
<?php
// URL: http://localhost/pagina.php?nome=João&idade=28&cidade=São+Paulo

echo "Nome: " . ($_GET['nome'] ?? 'Não informado') . "<br>\n";
echo "Idade: " . ($_GET['idade'] ?? 'Não informado') . "<br>\n";
echo "Cidade: " . ($_GET['cidade'] ?? 'Não informado') . "<br>\n";

// Iterar todos os parâmetros GET
foreach ($_GET as $chave => $valor) {
    echo htmlspecialchars($chave) . ": " . htmlspecialchars($valor) . "<br>\n";
}
```

> ⚠️ **Cuidado:** Dados em `$_GET` ficam visíveis na URL. Nunca envie senhas ou dados sensíveis via GET.

### Busca com formulário GET

```html
<!-- busca.php -->
<form method="get" action="busca.php">
    <label for="q">Buscar:</label>
    <input type="text" name="q" id="q"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Pesquisar</button>
</form>

<?php
if (!empty($_GET['q'])) {
    $termo = htmlspecialchars($_GET['q']);
    echo "<p>Você buscou por: <strong>{$termo}</strong></p>\n";
}
?>
```

---

## 3. `$_POST` — Dados no Corpo da Requisição

```html
<!-- cadastro.html -->
<form method="post" action="cadastro.php">
    <label>Nome:</label>
    <input type="text" name="nome" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Senha:</label>
    <input type="password" name="senha" required minlength="8">

    <button type="submit">Cadastrar</button>
</form>
```

```php
<?php
// cadastro.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.html');
    exit;
}

$nome  = $_POST['nome']  ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Validação básica
$erros = [];

if (trim($nome) === '') {
    $erros[] = 'O nome é obrigatório.';
}

if (trim($email) === '') {
    $erros[] = 'O email é obrigatório.';
}

if (strlen($senha) < 8) {
    $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
}

if (!empty($erros)) {
    foreach ($erros as $erro) {
        echo "<p style='color:red'>{$erro}</p>\n";
    }
    exit;
}

echo "<p>Cadastro realizado com sucesso!</p>\n";
echo "<p>Bem-vindo(a), " . htmlspecialchars($nome) . "!</p>\n";
```

---

## 4. `$_REQUEST` — Cuidados

`$_REQUEST` contém dados de `$_GET`, `$_POST` e `$_COOKIE`, na ordem definida pela diretiva `request_order` (padrão: `GP` — GET depois POST).

```php
<?php
// Evite usar $_REQUEST em produção — não é claro de onde os dados vêm.
$termo = $_REQUEST['termo'] ?? '';

// Prefira ser explícito:
$termo = $_GET['termo'] ?? $_POST['termo'] ?? '';
```

> ⚠️ **Cuidado:** `$_REQUEST` pode ser alterado por cookies, o que pode causar comportamentos inesperados. Em código de produção, sempre acesse a superglobal específica.

---

## 5. `$_SERVER` — Informações da Requisição

```php
<?php
// Método HTTP
echo "Método: {$_SERVER['REQUEST_METHOD']}<br>\n";

// URI da requisição
echo "URI: {$_SERVER['REQUEST_URI']}<br>\n";

// Cabeçalho Host
echo "Host: {$_SERVER['HTTP_HOST']}<br>\n";

// User-Agent do cliente
echo "Navegador: {$_SERVER['HTTP_USER_AGENT']}<br>\n";

// IP do cliente (considerando proxies)
$ip = $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['HTTP_CLIENT_IP']
    ?? $_SERVER['REMOTE_ADDR'];
echo "IP: {$ip}<br>\n";

// Referrer (página anterior)
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Nenhum';
echo "Veio de: {$referrer}<br>\n";

// Protocolo (HTTP ou HTTPS)
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$protocolo = $https ? 'https' : 'http';
echo "Protocolo: {$protocolo}<br>\n";

// URL completa atual
$urlAtual = "{$protocolo}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
echo "URL: {$urlAtual}<br>\n";

// Raiz do documento
echo "Document Root: {$_SERVER['DOCUMENT_ROOT']}<br>\n";

// IP e porta do servidor
echo "Servidor: {$_SERVER['SERVER_ADDR']}:{$_SERVER['SERVER_PORT']}<br>\n";

// Nome e caminho do script atual
echo "Script: {$_SERVER['SCRIPT_FILENAME']}<br>\n";
echo "Nome do script: {$_SERVER['SCRIPT_NAME']}<br>\n";
```

### Chaves Úteis de `$_SERVER`

| Chave | Descrição | Exemplo |
|-------|-----------|---------|
| `REQUEST_METHOD` | Método HTTP | `GET`, `POST` |
| `REQUEST_URI` | URI da requisição | `/pagina.php?id=1` |
| `HTTP_HOST` | Hostname | `localhost:8080` |
| `REMOTE_ADDR` | IP do cliente | `192.168.1.10` |
| `HTTP_REFERER` | Página de origem | `https://google.com` |
| `DOCUMENT_ROOT` | Raiz do site | `/var/www/html` |
| `CONTENT_TYPE` | Tipo de conteúdo | `application/json` |

---

## 6. `$_ENV` e `$GLOBALS`

### `$_ENV` — Variáveis de ambiente

```php
<?php
// Definidas no .env, no docker-compose ou no sistema operacional
// Exemplo: export APP_DEBUG=true && php script.php

$modoDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
if ($modoDebug) {
    echo "Modo debug ativo<br>\n";
}

// Senha do banco NUNCA no código
$dbSenha = $_ENV['DB_PASSWORD'] ?? '';
```

> 💡 **Dica:** Use bibliotecas como `vlucas/phpdotenv` para carregar variáveis de um arquivo `.env`. Nunca comite o `.env` — adicione ao `.gitignore`.

### `$GLOBALS` — Todas as variáveis do escopo global

```php
<?php
$config = ['app' => 'MeuApp', 'versao' => '3.0'];
$dbHost = 'localhost';

// $GLOBALS contém TODAS as variáveis globais
echo $GLOBALS['config']['app']; // MeuApp
echo $GLOBALS['dbHost'];        // localhost

// Também contém as superglobais
print_r($GLOBALS['_SERVER']);
```

---

## 7. GET vs POST: Semântica HTTP

| Característica | GET | POST |
|----------------|-----|------|
| **Visibilidade** | Dados na URL | Dados no corpo |
| **Cache** | Cacheável | Não cacheável |
| **Histórico** | Fica no histórico | Não fica |
| **Tamanho** | Limitado (~2048 chars na URL) | Sem limite prático (definido no servidor) |
| **Idempotente** | Sim | Não |
| **Uso típico** | Busca, filtros, paginação | Cadastro, login, envio de dados |

```php
<?php
// GET: buscar, listar, filtrar — não altera estado
// POST: criar, atualizar, deletar — altera estado

// Exemplo de roteamento por método
$metodo = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($metodo === 'GET' && $path === '/usuarios') {
    // Listar usuários
} elseif ($metodo === 'POST' && $path === '/usuarios') {
    // Criar usuário
}
```

---

## 8. Criando Formulários HTML com PHP

### Formulário que submete para si mesmo

```php
<?php
// contato.php — processa o formulário na mesma página
$enviado = false;
$erros = [];
$nome = $email = $mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome === '') {
        $erros[] = 'O nome é obrigatório.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um email válido.';
    }

    if (strlen($mensagem) < 10) {
        $erros[] = 'A mensagem deve ter pelo menos 10 caracteres.';
    }

    if (empty($erros)) {
        $enviado = true;
        // Aqui você salvaria no banco ou enviaria email
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contato</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; }
        .erro { color: red; }
        .sucesso { color: green; }
        label { display: block; margin-top: 12px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Contato</h1>

    <?php if ($enviado): ?>
        <p class="sucesso">Mensagem enviada com sucesso! Obrigado, <?= htmlspecialchars($nome) ?>.</p>
    <?php else: ?>
        <?php foreach ($erros as $erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endforeach; ?>

        <form method="post" action="contato.php" novalidate>
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome"
                   value="<?= htmlspecialchars($nome) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email) ?>">

            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows="5"><?= htmlspecialchars($mensagem) ?></textarea>

            <button type="submit">Enviar</button>
        </form>
    <?php endif; ?>
</body>
</html>
```

---

## 9. Filtros: `filter_var()` e `filter_input()`

PHP oferece a extensão **filter** para validar e sanitizar dados de forma consistente.

### Validação com `filter_var()`

```php
<?php
// Validações comuns
$email    = 'teste@exemplo.com';
$url      = 'https://www.php.net';
$ip       = '192.168.0.1';
$inteiro  = '42';
$booleano = 'yes';

var_dump(filter_var($email, FILTER_VALIDATE_EMAIL));      // 'teste@exemplo.com' (válido)
var_dump(filter_var('email-invalido', FILTER_VALIDATE_EMAIL)); // false
var_dump(filter_var($url, FILTER_VALIDATE_URL));          // 'https://www.php.net'
var_dump(filter_var($ip, FILTER_VALIDATE_IP));            // '192.168.0.1'
var_dump(filter_var($inteiro, FILTER_VALIDATE_INT));      // 42
var_dump(filter_var('42.5', FILTER_VALIDATE_INT));        // false

// Validar número com range
$idade = 25;
var_dump(filter_var($idade, FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 0,
        'max_range' => 150,
    ]
])); // 25

// Validar com flags
var_dump(filter_var($booleano, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE));
// true ('yes' conta como verdadeiro)

// Valores considerados true: '1', 'true', 'on', 'yes'
// Valores considerados false: '0', 'false', 'off', 'no', ''
```

### Sanitização com `filter_var()`

```php
<?php
// Sanitização — limpa o valor removendo caracteres indesejados

$email   = 'joão@@exemplo.com<script>';
$string  = '<h1>Olá!</h1><script>alert("xss")</script>';
$url     = 'https://exemplo.com/<script>';
$numero  = '+55 (11) 99999-8888';

// Remove caracteres inválidos de email
echo filter_var($email, FILTER_SANITIZE_EMAIL);
// joão@@exemplo.comscript — removeu <> mas manteve caracteres Unicode

// Remove tags HTML
echo filter_var($string, FILTER_SANITIZE_STRING);
// Olá!alert("xss") — removeu as tags

// Sanitiza URL removendo caracteres inválidos
echo filter_var($url, FILTER_SANITIZE_URL);
// https://exemplo.com/script

// Remove tudo exceto dígitos, + e -
echo filter_var($numero, FILTER_SANITIZE_NUMBER_INT);
// +5511999998888

// Remove tudo exceto dígitos e caracteres de float (.,e,E,+,-)
echo filter_var('R$ 1.299,90', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
// 1.29990
```

### `filter_input()` — Validar das superglobais

```php
<?php
// URL: http://localhost/pagina.php?id=42&email=teste@exemplo.com&cor=%23ff0000

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    die('ID inválido.');
}

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);

$cor = filter_input(INPUT_GET, 'cor', FILTER_SANITIZE_STRING);

// filter_input_array — validar múltiplos campos de uma vez
$filtros = [
    'nome'  => FILTER_SANITIZE_STRING,
    'email' => FILTER_VALIDATE_EMAIL,
    'idade' => [
        'filter'  => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0, 'max_range' => 150],
    ],
];

$dados = filter_input_array(INPUT_POST, $filtros);

foreach ($dados as $campo => $valor) {
    if ($valor === false || $valor === null) {
        echo "Campo '{$campo}' é inválido.<br>\n";
    }
}
```

### Tabela de Filtros Úteis

| Filtro de Validação | Filtro de Sanitização |
|---------------------|----------------------|
| `FILTER_VALIDATE_BOOL` | `FILTER_SANITIZE_STRING` |
| `FILTER_VALIDATE_DOMAIN` (PHP 7+) | `FILTER_SANITIZE_EMAIL` |
| `FILTER_VALIDATE_EMAIL` | `FILTER_SANITIZE_URL` |
| `FILTER_VALIDATE_FLOAT` | `FILTER_SANITIZE_NUMBER_INT` |
| `FILTER_VALIDATE_INT` | `FILTER_SANITIZE_NUMBER_FLOAT` |
| `FILTER_VALIDATE_IP` | `FILTER_SANITIZE_ENCODED` |
| `FILTER_VALIDATE_MAC` | `FILTER_SANITIZE_SPECIAL_CHARS` |
| `FILTER_VALIDATE_REGEXP` | |
| `FILTER_VALIDATE_URL` | |

---

## 10. Validação e Sanitização na Prática

```php
<?php
class ValidadorFormulario {
    private array $erros = [];

    public function validar(array $dados, array $regras): array {
        $dadosLimpos = [];

        foreach ($regras as $campo => $regrasCampo) {
            $valor = $dados[$campo] ?? '';

            // Campo obrigatório
            if (in_array('required', $regrasCampo) && trim((string) $valor) === '') {
                $this->erros[$campo] = "O campo '{$campo}' é obrigatório.";
                continue;
            }

            // Sanitização padrão: remover tags e espaços extras
            $valor = trim(strip_tags((string) $valor));

            // Email
            if (in_array('email', $regrasCampo) && $valor !== '') {
                $emailValidado = filter_var($valor, FILTER_VALIDATE_EMAIL);
                if ($emailValidado === false) {
                    $this->erros[$campo] = "Email inválido.";
                } else {
                    $valor = $emailValidado;
                }
            }

            // Inteiro
            if (in_array('int', $regrasCampo)) {
                $int = filter_var($valor, FILTER_VALIDATE_INT);
                if ($int === false) {
                    $this->erros[$campo] = "Deve ser um número inteiro.";
                } else {
                    $valor = $int;
                }
            }

            // Comprimento mínimo
            foreach ($regrasCampo as $regra) {
                if (str_starts_with($regra, 'min:')) {
                    $min = (int) substr($regra, 4);
                    if (strlen((string) $valor) < $min) {
                        $this->erros[$campo] = "Deve ter no mínimo {$min} caracteres.";
                    }
                }
                if (str_starts_with($regra, 'max:')) {
                    $max = (int) substr($regra, 4);
                    if (strlen((string) $valor) > $max) {
                        $this->erros[$campo] = "Deve ter no máximo {$max} caracteres.";
                    }
                }
            }

            $dadosLimpos[$campo] = $valor;
        }

        return $dadosLimpos;
    }

    public function temErros(): bool {
        return !empty($this->erros);
    }

    public function getErros(): array {
        return $this->erros;
    }
}

// Uso
$validador = new ValidadorFormulario();
$regras = [
    'nome'  => ['required', 'min:3', 'max:100'],
    'email' => ['required', 'email'],
    'idade' => ['int', 'min:0', 'max:150'],
];
$dados = $validador->validar($_POST, $regras);

if ($validador->temErros()) {
    print_r($validador->getErros());
} else {
    echo "Dados válidos!";
    print_r($dados);
}
```

---

## 11. CSRF Básico (Cross-Site Request Forgery)

```php
<?php
session_start();

// Gera token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function gerarCampoCSRF(): string {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verificarCSRF(): void {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Token CSRF inválido. Requisição rejeitada.');
    }
}

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    // Processa os dados com segurança...
    echo "Ação executada com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Formulário Protegido</title></head>
<body>
    <form method="post">
        <?= gerarCampoCSRF() ?>
        <label>Nome: <input type="text" name="nome"></label>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
```

> 💡 **Dica:** A função `hash_equals()` faz comparação em tempo constante, prevenindo **timing attacks** ao comparar tokens.

---

## 12. Redirecionamento com `header()`

```php
<?php
// Redirecionamento simples
header('Location: /pagina-destino.php');
exit; // SEMPRE chame exit/die após header('Location: ...')

// Redirecionamento com código HTTP
header('Location: /nova-pagina.php', true, 301); // 301 = permanente
exit;

// Redirecionar de volta para a página anterior
$referrer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
header("Location: {$referrer}");
exit;

// Redirecionamento com mensagem flash (ver Módulo 13 — Sessões)
session_start();
$_SESSION['flash_mensagem'] = 'Operação realizada com sucesso!';
header('Location: /index.php');
exit;
```

> ⚠️ **Cuidado:** `header()` deve ser chamado **antes de qualquer output** (HTML, echo, espaços em branco). Caso contrário, gerará erro "headers already sent".

---

## 13. `$_FILES` para Upload (Resumo)

```php
<?php
// Ver Módulo 11 para detalhes completos

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $arquivo = $_FILES['documento'];

    $nomeTemporario = $arquivo['tmp_name'];
    $nomeOriginal   = $arquivo['name'];
    $tamanho        = $arquivo['size'];
    $erro           = $arquivo['error'];

    if ($erro === UPLOAD_ERR_OK) {
        $destino = __DIR__ . '/uploads/' . basename($nomeOriginal);
        move_uploaded_file($nomeTemporario, $destino);
        echo "Upload concluído!";
    } else {
        echo "Erro no upload: código {$erro}";
    }
}
?>
```

---

## 14. `request_parse_body()` (PHP 8.4+)

> **PHP 8.4+**

A partir do PHP 8.4, a função `request_parse_body()` pode ser usada para processar o corpo da requisição de forma explícita, sendo útil em APIs e SAPI diferentes (CGI, FastCGI, etc.).

```php
<?php
// PHP 8.4+
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$postData, $fileData] = request_parse_body();

    // $postData contém os campos do formulário (equivalente a $_POST)
    // $fileData contém os arquivos enviados (equivalente a $_FILES)

    $nome = $postData['nome'] ?? '';

    if (isset($fileData['documento'])) {
        $arquivo = $fileData['documento'];
        move_uploaded_file($arquivo['tmp_name'], __DIR__ . '/uploads/' . $arquivo['name']);
    }
}
```

> 💡 **Dica:** `request_parse_body()` é útil quando você está rodando PHP em modo CLI ou em servidores CGI onde `$_POST` pode não estar disponível.

---

## 15. Exemplo Completo: Newsletter com Validação e CSRF

```php
<?php
// newsletter.php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erros = [];
$sucesso = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erros[] = 'Token de segurança inválido.';
    }

    // Validação
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $erros[] = 'Informe um email válido.';
    }

    // Verifica se já está cadastrado (simulação com arquivo)
    $arquivoInscritos = __DIR__ . '/newsletter.txt';
    $inscritos = file_exists($arquivoInscritos)
        ? file($arquivoInscritos, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if (in_array($email, $inscritos)) {
        $erros[] = 'Este email já está cadastrado.';
    }

    if (empty($erros)) {
        file_put_contents($arquivoInscritos, $email . "\n", FILE_APPEND | LOCK_EX);
        $sucesso = true;
        $email = '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; display: flex;
               justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 400px; width: 100%; }
        h2 { margin-bottom: 0.5rem; }
        p.description { color: #666; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        input[type="email"] { width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 4px;
                              font-size: 1rem; }
        button { width: 100%; padding: 0.7rem; background: #2563eb; color: white; border: none;
                 border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .erro { color: #dc2626; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .sucesso { color: #16a34a; font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="card">
    <?php if ($sucesso): ?>
        <h2>Obrigado por se inscrever!</h2>
        <p class="sucesso">Você receberá nossas novidades em breve.</p>
    <?php else: ?>
        <h2>Assine nossa Newsletter</h2>
        <p class="description">Receba dicas de PHP direto no seu email. Sem spam.</p>

        <?php foreach ($erros as $erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="field">
                <label for="email">Seu melhor email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>"
                       placeholder="exemplo@email.com" required>
            </div>
            <button type="submit">Inscrever-se</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
```

---

## 📚 Referências

- [PHP: Variáveis Predefinidas (Superglobais)](https://www.php.net/manual/pt_BR/reserved.variables.php)
- [PHP: Filtros — filter_var](https://www.php.net/manual/pt_BR/function.filter-var.php)
- [PHP: Filtros — filter_input](https://www.php.net/manual/pt_BR/function.filter-input.php)
- [PHP: header()](https://www.php.net/manual/pt_BR/function.header.php)
- [PHP: Upload de arquivos](https://www.php.net/manual/pt_BR/features.file-upload.php)
- [PHP: request_parse_body (8.4+)](https://www.php.net/manual/pt_BR/function.request-parse-body.php)
- [OWASP: CSRF](https://owasp.org/www-community/attacks/csrf)
- [PHP: hash_equals](https://www.php.net/manual/pt_BR/function.hash-equals.php)
