# 02 — Sintaxe Básica

## Tags PHP

O PHP pode ser mesclado com HTML. O interpretador só processa o código que está dentro das tags PHP.

### Tag padrão (`<?php ?>`)

Esta é a tag **recomendada** e **sempre disponível**:

```php
<?php
echo "This is PHP code.";
?>
<p>This is plain HTML.</p>
<?php
echo "Back to PHP.";
```

### Tag de echo curto (`<?= ?>`)

Equivalente a `<?php echo ...; ?>`, sempre disponível a partir do PHP 5.4:

```php
<p>Welcome, <?= htmlspecialchars($name) ?>!</p>
<p>Your age: <?= $age ?></p>
```

> 💡 **Dica**: `<?= ?>` é **sempre habilitado**, não importa a configuração
> `short_open_tag` no php.ini. Use à vontade em templates!

### Arquivos só com PHP

Arquivos que contêm **apenas** código PHP **não devem** ter a tag de fechamento `?>`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    // ...
}

// Sem tag de fechamento
```

Isso evita que espaços em branco acidentais após `?>` sejam enviados ao navegador,
o que quebraria headers HTTP e causaria o famoso erro "headers already sent".

---

## Comentários

### Linha única

```php
<?php

// This is a single-line comment (C++ style)

# This is also a single-line comment (shell/Perl style)

$name = "Anna"; // Comment after code on the same line
```

### Múltiplas linhas

```php
<?php

/*
 * Multi-line comment.
 * Useful for documenting functions.
 */

/*
   Another accepted format.
   Can have as many lines as you want.
*/

/**
 * DocBlock documentation comment.
 * Used by tools like phpDocumentor.
 *
 * @param  string $name   Username
 * @param  int    $age    User age
 * @return string         Formatted message
 */
function greeting(string $name, int $age): string
{
    return "Hello, {$name}! You are {$age} years old.";
}
```

> 💡 **Dica**: DocBlocks (`/** */`) são lidos por IDEs e ferramentas de
> documentação automática. Para funções, classes e métodos, use DocBlocks.

### Comentários não aninham

```php
<?php
/*
   echo "test";   /* This causes a parse error! */
*/
```

Comentários `/* */` **não podem ser aninhados**. O interpretador encontra o primeiro `*/` e fecha o comentário.

---

## Mesclando PHP com HTML

### Blocos condicionais

```php
<?php $loggedIn = true; ?>

<?php if ($loggedIn): ?>
    <nav>
        <a href="/profile">My Profile</a>
        <a href="/logout">Logout</a>
    </nav>
<?php else: ?>
    <a href="/login">Login</a>
<?php endif; ?>
```

### Loops em templates

```php
<ul>
    <?php foreach ($products as $product): ?>
        <li>
            <strong><?= htmlspecialchars($product['name']) ?></strong>
            — R$ <?= number_format($product['price'], 2, ',', '.') ?>
        </li>
    <?php endforeach; ?>
</ul>
```

### Separação de lógica e apresentação

```php
<?php
// prepare_data.php — entry point (run this file)
declare(strict_types=1);

$title = 'Home';
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob',   'email' => 'bob@example.com'],
];

require 'template.php';
```

`template.php` — presentation only, included by the script above

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($title) ?></h1>
    <ul>
    <?php foreach ($users as $user): ?>
        <li><?= htmlspecialchars($user['name']) ?></li>
    <?php endforeach; ?>
    </ul>
</body>
</html>
```

Rode no terminal:
```bash
php prepare_data.php
```

Ou com o servidor embutido:
```bash
php -S localhost:8080
# Acesse http://localhost:8080/prepare_data.php
```

> 💡 **Dica**: Sempre use `htmlspecialchars()` ao exibir dados do usuário ou
> vindos de banco no HTML. Isso previne ataques XSS (Cross-Site Scripting).

---

## `echo`, `print`, `var_dump`, `print_r`

### `echo`

```php
<?php

echo "Hello, world!\n";

echo "Hello", " ", "world!";  // Accepts multiple arguments (comma-separated)

echo "Hello" . " " . "world!"; // Or concatenation

// With expressions
$name = "Carlos";
echo "Welcome, " . $name . "!";
echo "Welcome, {$name}!";        // Interpolation (double quotes)
echo 'Welcome, ' . $name . '!';  // Single quotes + concatenation
```

`echo` não é uma função — é uma **construção da linguagem** (*language construct*).

Enquanto funções são blocos registrados na tabela de símbolos (com overhead
de call stack, podem ser callback, seguem a sintaxe `nome(args)`), construções
da linguagem são **parte da sintaxe do interpretador** — palavras reservadas que
o parser reconhece diretamente na compilação, antes da execução.

Algumas portas que isso abre:

1. **Sintaxe especial**. `echo` aceita múltiplos argumentos separados por
   vírgula (`echo $a, $b, $c`) sem precisar de array. `isset($x)` não dispara
   warning se `$x` não existe (uma função dispararia, porque o argumento seria
   avaliado antes da chamada).

2. **Não pode ser callback**. `array_map('echo', $arr)` quebra, mas
   `array_map('strlen', $arr)` funciona. Você não passa construção da linguagem
   como `callable`.

3. **Sem overhead de chamada de função**. Irrelevante em 99% dos casos, mas
   explica por que `echo` é marginalmente mais rápido que `print`.

Outras construções da linguagem: `print`, `include`, `require`,
`include_once`, `require_once`, `isset`, `empty`, `unset`, `die`, `exit`,
`list`, `return`, `yield`. Algumas *parecem* função porque aceitam
parênteses (`isset($x)`), mas não são.

> 💡 Se você já programa, pense nelas como o equivalente a *keywords* ou
> *built-in statements* de outras linguagens — como `return` ou `yield`,
> que não são funções, são sintaxe da linguagem.

### `print`

```php
<?php

print "Hello, world!\n";

print("Hello, world!\n"); // Accepts parentheses, but only 1 argument

// print returns 1 (always), echo returns nothing
$result = print "test";
echo $result; // 1
```

| Característica   | `echo`                 | `print`                 |
|------------------|------------------------|-------------------------|
| Tipo             | Construção da linguagem | Construção da linguagem |
| Retorno          | Nenhum                 | Sempre `1`              |
| Múltiplos args   | Sim (`echo $a, $b`)    | Não                     |
| Performance      | Um pouco mais rápido | Um pouco mais lento |

### `var_dump()`

Exibe informações detalhadas sobre uma variável: tipo e valor.

```php
<?php

$name = "Anna";
var_dump($name);
// string(4) "Anna"

$age = 30;
var_dump($age);
// int(30)

$price = 19.99;
var_dump($price);
// float(19.99)

$fruits = ['apple', 'banana', 'orange'];
var_dump($fruits);
// array(3) {
//   [0]=> string(5) "apple"
//   [1]=> string(6) "banana"
//   [2]=> string(6) "orange"
// }

$active = true;
var_dump($active);
// bool(true)

$null = null;
var_dump($null);
// NULL
```

```php
<?php
// var_dump accepts multiple arguments:
var_dump($name, $age, $price);
```

> 💡 **Dica**: `var_dump()` é sua melhor amiga na hora de debugar. Use e abuse!

### `print_r()`

Exibe variáveis em formato legível, pensado para leitura humana — ao contrário
do `var_dump()`, ele **esconde tipo e tamanho** e foca só no conteúdo,
indentando arrays e objetos de forma organizada.

Por padrão, manda pra tela. Com o segundo parâmetro `true`, retorna como
string (útil pra logging).

```php
<?php

$data = [
    'name'  => 'Beatrice',
    'age'   => 28,
    'city'  => 'New York',
    'hobbies' => ['reading', 'music', 'running'],
];

print_r($data);

// Return as string instead of displaying
$text = print_r($data, true);
```

---

## Constantes

### `define()`

```php
<?php

define('APP_NAME', 'MySystem');
define('VERSION', '1.0.0');
define('SESSION_TIME', 3600);

echo APP_NAME;   // MySystem
echo VERSION;     // 1.0.0

// echo $APP_NAME; // Undefined variable — constants don't use $
```

```php
<?php
// define() at runtime

$environment = 'production';

if ($environment === 'production') {
    define('DEBUG_MODE', false);
} else {
    define('DEBUG_MODE', true);
}

var_dump(DEBUG_MODE); // bool(false)
```

### `const` (palavra-chave)

```php
<?php

const SERVICE_RATE = 0.10;
const PI = 3.14159;

echo SERVICE_RATE * 100 . '%'; // 10%
```

### Diferenças entre `define()` e `const`

| Característica          | `define()`                           | `const`                           |
|-------------------------|--------------------------------------|-----------------------------------|
| Escopo                  | Global                                | Namespace/classe atual             |
| Runtime                 | Pode ser definida em qualquer lugar   | Deve ser definida no top-level    |
| Expressões              | Aceita qualquer expressão             | Aceita apenas valores escalares e arrays constantes |
| Namespace               | Precisa do namespace no nome          | Respeita o namespace atual        |
| Lista de constantes     | `get_defined_constants()`             | `get_defined_constants()`         |
| `defined()`             | Sim                                   | Sim                               |

```php
<?php

namespace App\Config;

// const honors the namespace
const APP_NAME = 'MyApp';
// Accessible as \App\Config\APP_NAME

// define is always global
define('APP_VERSION', '2.0');

// define with expression (const doesn't allow this)
define('TIMESTAMP', time());

// define inside a function (const doesn't allow this)
function init(): void
{
    define('INITIALIZED', true);
}
init();
var_dump(defined('INITIALIZED')); // bool(true)
```

### Constantes mágicas

O PHP fornece constantes predefinidas que mudam conforme o contexto:

```php
<?php

echo __LINE__;     // Current line number in the file
echo __FILE__;     // Full path of the file
echo __DIR__;      // Directory of the file (PHP 5.3+)
echo __FUNCTION__; // Current function name
echo __CLASS__;    // Current class name (includes namespace)
echo __METHOD__;   // Current method name (Class::method)
echo __NAMESPACE__;// Current namespace
echo __TRAIT__;    // Current trait name (PHP 5.4+)
```

```php
<?php

namespace App\Util;

function whereAmI(): void
{
    echo "Function: " . __FUNCTION__ . "\n";    // App\Util\whereAmI
    echo "Namespace: " . __NAMESPACE__ . "\n"; // App\Util
    echo "File: " . __FILE__ . "\n";
    echo "Line: " . __LINE__ . "\n";
}

whereAmI();
```

---

## `include`, `require`, `include_once`, `require_once`

Estas construções permitem incluir o conteúdo de um arquivo PHP dentro de outro.

### `include`

Inclui e avalia o arquivo. Se o arquivo não for encontrado, **emite um warning** (E_WARNING) e o script **continua** executando.

```php
<?php
include 'header.php';
include 'footer.php';
```

### `require`

Igual ao `include`, mas se o arquivo não for encontrado, **emite um erro fatal** (E_ERROR) e o script **para**.

```php
<?php
require 'config/database.php';
```

### `include_once` e `require_once`

Garantem que o arquivo seja incluído **apenas uma vez**, evitando redefinição de funções/classes.

```php
<?php
require_once 'functions.php';
require_once 'functions.php';  // Already included, skipped
```

> `_once` foi adicionado depois para resolver o problema de redeclaração:
> se `functions.php` define `function connect()` e você der `require` duas
> vezes, o PHP explode com **"Cannot redeclare function"**. Classes também.
> O `_once` mantém uma tabela interna de paths já incluídos e pula se já
> conhece aquele arquivo.
> 
> Ainda existem usos para `include`/`require` sem `_once`:
> 
> - **Templates de repetição**: renderizar o mesmo card 50 vezes com
>   `include 'card.php'` — você *quer* que execute a cada loop.
> - **Micro-otimização histórica**: o `_once` faz lookup em toda chamada.
>   Era relevante no PHP 4, hoje é irrelevante, mas a API ficou.
>
> **Regra prática**: use `require_once` como padrão e não se arrependa.

| Instrução       | Arquivo não encontrado | Comportamento           | Repetição      |
|-----------------|------------------------|-------------------------|----------------|
| `include`       | Warning (continua)     | Inclui e avalia         | Sempre inclui  |
| `include_once`  | Warning (continua)     | Inclui e avalia         | Só uma vez     |
| `require`       | Fatal error (para)     | Inclui e avalia         | Sempre inclui  |
| `require_once`  | Fatal error (para)     | Inclui e avalia         | Só uma vez     |

### Exemplo prático com caminhos

```php
<?php

// Relative path (relative to current script)
require_once 'config.php';

// Absolute path
require_once '/var/www/app/functions.php';

// Using __DIR__ for safe relative paths
require_once __DIR__ . '/../vendor/autoload.php';

// include inside a function
function loadTemplate(string $name): string
{
    ob_start();
    include __DIR__ . "/templates/{$name}.php";
    return ob_get_clean();
}
```

> 💡 **Dica**: Sempre use `require_once` para arquivos de configuração, funções e
> classes. Para templates HTML que podem ser chamados múltiplas vezes, use `include`.

---

## Namespaces

Namespaces organizam classes, funções e constantes em grupos lógicos, evitando conflitos de nomes.

### Declarando um namespace

```php
<?php

namespace App\Models;

class User
{
    public function __construct(
        public string $name
    ) {}
}

// Full name: \App\Models\User
```

```php
<?php

namespace App\Services;

class User
{
    // This class does NOT conflict with \App\Models\User
    // because it's in a different namespace
}
```

### Namespace aninhado

```php
<?php

namespace Project\Module\Submodule;

// Full name: \Project\Module\Submodule\MyClass
class MyClass {}
```

### `use` — importando namespaces

```php
<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\EmailService;
use App\Util\Validation as Val;

class UserController
{
    public function register(string $name, string $email): User
    {
        Val::email($email);

        $user = new User($name);
        $emailService = new EmailService();
        $emailService->sendWelcome($email);

        return $user;
    }
}
```

### `use` com funções e constantes (PHP 5.6+)

```php
<?php

namespace App\Example;

use function App\Util\formatCurrency;
use const App\Config\INTEREST_RATE;

echo formatCurrency(100 * INTEREST_RATE);
```

### Namespace global

```php
<?php

namespace App\Library;

// \ before class name references the global namespace
$date = new \DateTime('now');
echo $date->format('d/m/Y');

// Without the \, PHP would look for \App\Library\DateTime (which doesn't exist)
```

> ⚠️ **Cuidado**: Dentro de um namespace, todas as referências a classes são
> relativas ao namespace atual. Para classes nativas do PHP (`DateTime`, `PDO`,
> `Exception`) use `\` prefixo ou importe com `use`.

---

## Convenções de nomenclatura

Seguindo os padrões PSR-1 e PSR-12:

```php
<?php

// Classes: PascalCase
class ProductController {}
class EmailService {}
class HttpClient {}

// Métodos e funções: camelCase
public function calculateTotal(): float {}
public function findById(int $id): ?object {}
function formatPhone(string $tel): string {}

// Variáveis: camelCase
$fullName = 'Maria Silva';
$stockQuantity = 42;
$isActive = true;

// Constantes: UPPER_SNAKE_CASE
const DEFAULT_LIMIT = 100;
const BASE_URL = 'https://api.example.com';
define('MAX_ATTEMPTS', 3);

// Propriedades: camelCase
private string $birthDate;
public float $unitPrice;

// Namespaces: PascalCase com vendor prefix
namespace MyApp\Services\Payment;
namespace Acme\Util\Formatting;
```

### Nomes de arquivos

```bash
# Uma classe por arquivo. Nome do arquivo = nome da classe
src/
├── Models/
│   ├── User.php          # class User
│   ├── Product.php          # class Product
│   └── CartItem.php     # class CartItem
├── Controllers/
│   └── HomeController.php   # class HomeController
└── Services/
    └── EmailService.php     # class EmailService
```

---

## 📚 Referências

- **Sintaxe básica**: [php.net/manual/pt_BR/language.basic-syntax.php](https://www.php.net/manual/pt_BR/language.basic-syntax.php)
- **Constantes**: [php.net/manual/pt_BR/language.constants.php](https://www.php.net/manual/pt_BR/language.constants.php)
- **Namespaces**: [php.net/manual/pt_BR/language.namespaces.php](https://www.php.net/manual/pt_BR/language.namespaces.php)
- **PSR-1**: [php-fig.org/psr/psr-1](https://www.php-fig.org/psr/psr-1/)
- **PSR-12**: [php-fig.org/psr/psr-12](https://www.php-fig.org/psr/psr-12/)
- **include/require**: [php.net/manual/pt_BR/function.include.php](https://www.php.net/manual/pt_BR/function.include.php)
- **Magic constants**: [php.net/manual/pt_BR/language.constants.magic.php](https://www.php.net/manual/pt_BR/language.constants.magic.php)

---

## Próximo módulo

[→ 03 — Tipos de Dados e Variáveis](./03-tipos-de-dados-e-variaveis.md)
