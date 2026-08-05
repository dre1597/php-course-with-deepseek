# 02 — Sintaxe Básica

## Tags PHP

O PHP pode ser mesclado com HTML. O interpretador só processa o código que está dentro das tags PHP.

### Tag padrão (`<?php ?>`)

Esta é a tag **recomendada** e **sempre disponível**:

```php
<?php
echo "Este código é PHP.";
?>
<p>Este é HTML puro.</p>
<?php
echo "Voltamos ao PHP.";
```

### Tag de echo curto (`<?= ?>`)

Equivalente a `<?php echo ...; ?>`, sempre disponível a partir do PHP 5.4:

```php
<p>Bem-vindo, <?= htmlspecialchars($name) ?>!</p>
<p>Sua idade: <?= $age ?></p>
```

> 💡 **Dica**: `<?= ?>` é **sempre habilitado**, não importa a configuração
> `short_open_tag` no php.ini. Use à vontade em templates!

### Tag curta (`<? ?>`) — EVITE

```php
<? echo "Isso depende de short_open_tag = On"; ?>
```

> ⚠️ **Cuidado**: A tag `<? ?>` depende da configuração `short_open_tag` e
> conflita com a declaração XML `<?xml ?>`. **Não use**.

### Tag ASP (`<% %>`) — REMOVIDA

Removida no PHP 7. Não use de forma alguma.

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

// Sem tag de fechamento ?>
```

Isso evita que espaços em branco acidentais após `?>` sejam enviados ao navegador,
o que quebraria headers HTTP e causaria o famoso erro "headers already sent".

---

## Instruções e ponto-e-vírgula

Cada instrução PHP termina com **ponto-e-vírgula** (`;`):

```php
<?php

$name = "João";              // instrução 1
echo $name;                  // instrução 2
$age = 25;                 // instrução 3
```

A última instrução de um bloco PHP pode omitir o `;` **apenas se seguida da tag de fechamento**:

```php
<?php echo "OK" ?>   <!-- Funciona: o ?> fecha a instrução -->
<?php echo "OK"; ?>  <!-- Melhor: explícito -->
```

> 💡 **Dica**: Sempre use `;`. É mais consistente e evita bugs sutis.

### Múltiplas instruções na mesma linha

```php
<?php
$x = 5; $y = 10; $z = $x + $y;  // Válido, mas evite por legibilidade
```

---

## Comentários

### Linha única

```php
<?php

// Este é um comentário de linha única (estilo C++)

# Este também é um comentário de linha única (estilo shell/Perl)

$name = "Ana"; // Comentário após código na mesma linha
```

### Múltiplas linhas

```php
<?php

/*
 * Este é um comentário
 * de múltiplas linhas.
 * Muito útil para documentar funções.
 */

/*
   Outro formato aceito.
   Pode ter quantas linhas quiser.
*/

/**
 * Comentário de documentação (DocBlock).
 * Usado por ferramentas como phpDocumentor.
 *
 * @param  string $name   Nome do usuário
 * @param  int    $age  Idade do usuário
 * @return string         Mensagem formatada
 */
function greeting(string $name, int $age): string
{
    return "Olá, {$name}! Você tem {$age} anos.";
}
```

> 💡 **Dica**: DocBlocks (`/** */`) são lidos por IDEs e ferramentas de
> documentação automática. Para funções, classes e métodos, use DocBlocks.

### Comentários não aninham

```php
<?php
/*
   echo "A";   /* Isto causa erro de parse! */
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
        <a href="/perfil">Meu Perfil</a>
        <a href="/sair">Sair</a>
    </nav>
<?php else: ?>
    <a href="/login">Entrar</a>
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
// prepare_data.php — apenas lógica
declare(strict_types=1);

$title = 'Página Inicial';
$users = [
    ['name' => 'Alice', 'email' => 'alice@exemplo.com'],
    ['name' => 'Bob',   'email' => 'bob@exemplo.com'],
];
?>

<!-- template.php — apenas apresentação -->
<!DOCTYPE html>
<html lang="pt-BR">
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

> 💡 **Dica**: Sempre use `htmlspecialchars()` ao exibir dados do usuário ou
> vindos de banco no HTML. Isso previne ataques XSS (Cross-Site Scripting).

---

## `echo`, `print`, `var_dump`, `print_r`

### `echo`

```php
<?php

echo "Olá, mundo!\n";

echo "Olá", " ", "mundo!";  // Aceita múltiplos argumentos (separados por vírgula)

echo "Olá" . " " . "mundo!"; // Ou concatenação

// Com expressões
$name = "Carlos";
echo "Bem-vindo, " . $name . "!";
echo "Bem-vindo, {$name}!";        // Interpolação (aspas duplas)
echo 'Bem-vindo, ' . $name . '!';  // Aspas simples + concatenação
```

`echo` não é uma função — é uma **construção da linguagem**. Por isso aceita múltiplos parâmetros sem parênteses.

### `print`

```php
<?php

print "Olá, mundo!\n";

print("Olá, mundo!\n"); // Aceita parênteses, mas só 1 argumento

// print retorna 1 (sempre), echo não retorna nada
$result = print "teste";
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

$name = "Ana";
var_dump($name);
// string(3) "Ana"

$age = 30;
var_dump($age);
// int(30)

$price = 19.99;
var_dump($price);
// float(19.99)

$fruits = ['maçã', 'banana', 'laranja'];
var_dump($fruits);
// array(3) {
//   [0]=> string(5) "maçã"
//   [1]=> string(6) "banana"
//   [2]=> string(7) "laranja"
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
// var_dump pode receber múltiplos argumentos:
var_dump($name, $age, $price);
```

> 💡 **Dica**: `var_dump()` é sua melhor amiga na hora de debugar. Use e abuse!

### `print_r()`

Similar ao `var_dump()`, mas com formatação mais legível para arrays. Por padrão, mostra o resultado na tela; com o segundo parâmetro `true`, retorna como string.

```php
<?php

$data = [
    'name'  => 'Beatriz',
    'age'   => 28,
    'city'  => 'São Paulo',
    'hobbies' => ['leitura', 'música', 'corrida'],
];

print_r($data);

// Retornar como string em vez de exibir
$text = print_r($data, true);
// Agora você pode, por exemplo, logar:
// error_log($text);
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

// Constantes não usam $
// echo $APP_NAME; // Isso NÃO funcionaria (variável não definida)
```

```php
<?php
// define() em tempo de execução (runtime)
// Pode ser definido dentro de if, funções, etc.

$environment = 'producao';

if ($environment === 'producao') {
    define('DEBUG_MODE', false);
} else {
    define('DEBUG_MODE', true);
}

var_dump(DEBUG_MODE); // bool(false)
```

```php
<?php
// Terceiro parâmetro: case-insensitive (obsoleto desde PHP 7.3, removido no 8.0)
// NÃO USE: define('CONST', 'valor', true);
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

// const respeita o namespace
const APP_NAME = 'MeuApp';
// Acessível como \App\Config\APP_NAME

// define sempre é global
define('APP_VERSION', '2.0');
// Acessível como \APP_VERSION (global)

// define com expressão (const não permite isso)
define('TIMESTAMP', time());

// define dentro de função (const não permite)
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

echo __LINE__;     // Número da linha atual no arquivo
echo __FILE__;     // Caminho completo do arquivo
echo __DIR__;      // Diretório do arquivo (PHP 5.3+)
echo __FUNCTION__; // Nome da função atual
echo __CLASS__;    // Nome da classe atual (inclui namespace)
echo __METHOD__;   // Nome do método atual (Classe::metodo)
echo __NAMESPACE__;// Namespace atual
echo __TRAIT__;    // Nome do trait atual (PHP 5.4+)
```

```php
<?php

namespace App\Util;

function whereAmI(): void
{
    echo "Função: " . __FUNCTION__ . "\n";    // App\Util\whereAmI
    echo "Namespace: " . __NAMESPACE__ . "\n"; // App\Util
    echo "Arquivo: " . __FILE__ . "\n";
    echo "Linha: " . __LINE__ . "\n";
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
include 'header.php';   // Warning se não existir, mas continua
include 'footer.php';
```

### `require`

Igual ao `include`, mas se o arquivo não for encontrado, **emite um erro fatal** (E_ERROR) e o script **para**.

```php
<?php
require 'config/database.php';  // Erro fatal se não existir
```

### `include_once` e `require_once`

Garantem que o arquivo seja incluído **apenas uma vez**, evitando redefinição de funções/classes.

```php
<?php
require_once 'functions.php';
require_once 'functions.php';  // Ignorado — já foi incluído
```

| Instrução       | Arquivo não encontrado | Comportamento           | Repetição      |
|-----------------|------------------------|-------------------------|----------------|
| `include`       | Warning (continua)     | Inclui e avalia         | Sempre inclui  |
| `include_once`  | Warning (continua)     | Inclui e avalia         | Só uma vez     |
| `require`       | Fatal error (para)     | Inclui e avalia         | Sempre inclui  |
| `require_once`  | Fatal error (para)     | Inclui e avalia         | Só uma vez     |

### Exemplo prático com caminhos

```php
<?php

// Caminho relativo (em relação ao script atual)
require_once 'config.php';

// Caminho absoluto
require_once '/var/www/app/functions.php';

// Usando __DIR__ para caminho relativo seguro
require_once __DIR__ . '/../vendor/autoload.php';

// include dentro de função
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

// Nome completo: \App\Models\User
```

```php
<?php

namespace App\Services;

class User
{
    // Esta classe NÃO conflita com \App\Models\User
    // Porque está em outro namespace
}
```

### Namespace aninhado

```php
<?php

namespace Project\Module\Submodule;

// Nome completo: \Project\Module\Submodule\MyClass
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

// new \DateTime() — a barra invertida inicial referencia o namespace global
$date = new \DateTime('now');
echo $date->format('d/m/Y');

// Sem a barra, PHP procuraria \App\Library\DateTime (que não existe)
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
