# 03 — Tipos de Dados e Variáveis

## Variáveis

### Declaração e uso

Toda variável em PHP começa com `$`:

```php
<?php

$name = "Mary";
$age = 30;
$height = 1.72;
$active = true;
```

Nomes de variáveis são **case-sensitive**:

```php
<?php

$name = "John";
$alternateName = "Mary";
$alternateFullName = "Charles";

echo $name;
echo $alternateName;
echo $alternateFullName;
// Three different variables!
```

### Regras de nomenclatura

- Começam com `$` seguido de letra ou underscore (`_`)
- Depois podem conter letras, números e underscores
- **Não podem** começar com número
- Convenção: **camelCase** (`$meuNomeCompleto`)
- Unicode permitido (mas prefira ASCII por compatibilidade)

```php
<?php

// ✅ Valid
$_variable = 1;
$userName = "Anna";
$total2 = 100;
$café = "hot";              // Unicode — works, but avoid
$válida = "valid";
$specialChar = "special";   // Works, but terrible practice

// ❌ Invalid
// $1place = "error";       // Cannot start with a number
// $my-name = "error";      // Hyphen not allowed
// $my name = "error";      // Space not allowed
```

### Atribuição por valor vs referência

Por padrão, PHP atribui por **cópia** (igual C com structs). O `&` cria um
**alias** — mesma ideia de referência do C++, ambos apontam pro mesmo
`zval` interno.

```php
<?php

$first = 10;
$second = $first;   // Copy
$first = 20;
echo $second;       // 10 — independent copy

$first = 10;
$second = &$first;  // Reference (alias)
$first = 20;
echo $second;       // 20 — same zval

$second = 30;
echo $first;        // 30 — both see the change
```

#### Uso real: `foreach` com referência

O cenário mais comum é modificar elementos de um array **no lugar**,
sem criar array novo:

```php
<?php

$prices = [100, 200, 300];

// Without reference — does NOT modify $prices
foreach ($prices as $price) {
    $price *= 1.1;  // Modifies the copy, original untouched
}
print_r($prices);  // [100, 200, 300]

// With reference — modifies in-place
foreach ($prices as &$price) {
    $price *= 1.1;
}
unset($price);      // CRUCIAL: unset after foreach with reference!
print_r($prices);  // [110, 220, 330]
```

> ⚠️ O `unset($price)` depois do `foreach &` é obrigatório. Sem ele, `$price`
> continua como alias do último elemento e qualquer uso posterior da variável
> sobrescreve o array sem você perceber.

> 💡 **Por baixo dos panos**: toda variável PHP é representada internamente
> por um `zval` — uma struct do Zend Engine que guarda tipo, valor e
> refcount. Atribuição por valor aloca um `zval` novo e copia o dado.
> Atribuição por referência (`&`) faz as duas variáveis apontarem pro
> **mesmo** `zval` — é um alias, não uma cópia.
>
> ```c
> // struct interna do Zend Engine (simplificada)
> typedef struct _zval_struct {
>     zend_value        value;      // union: o dado em si
>     zend_uchar        type;       // IS_STRING, IS_LONG, etc.
>     zend_uchar        type_flags;
>     uint32_t          gc_info;    // refcount + flags
> } zval;
> ```

### Variáveis variáveis

```php
<?php

$field = "name";
$$field = "Beatrice";

echo $name;
echo $$field;
```

> ⚠️ **Cuidado**: Variáveis variáveis são confusas e quase nunca necessárias.
> Em 99% dos casos, use arrays associativos (`$data[$campo]`).

---

## Tipos de dados

O PHP tem 9 tipos de dados principais:

| Categoria  | Tipos                         |
|------------|-------------------------------|
| Escalares  | `int`, `float`, `bool`, `string` |
| Compostos  | `array`, `object`, `callable`, `iterable` |
| Especiais  | `null`, `resource`            |

### `int` (inteiro)

Números sem casas decimais. O tamanho depende da plataforma (32 ou 64 bits):

```php
<?php

$decimal = 42;
$negative = -17;
$binary = 0b1010;      // 10
$octal = 0o755;        // 493
$hex = 0xFF;           // 255
$thousands = 1_000_000;

echo PHP_INT_MAX;  // 9223372036854775807 (64-bit)
echo PHP_INT_MIN;  // -9223372036854775808
```

> 💡 **Dica**: Use `_` como separador de milhares para legibilidade:
> `$budget = 1_500_000;` no lugar de `$budget = 1500000;`

### `float` (ponto flutuante)

```php
<?php

$simple = 1.5;
$negativeFloat = -0.33;
$scientific = 1.2e3;
$tiny = 7E-10;
$imprecise = 0.1 + 0.2;  // 0.30000000000000004 (IEEE 754 imprecision)

echo PHP_FLOAT_MAX;
echo PHP_FLOAT_MIN;
```

> ⚠️ **Cuidado**: Operações com float podem ter imprecisão. Para cálculos
> financeiros, use strings e calcule em centavos, ou use a extensão BCMath,
> ou a extensão GMP, ou a extensão Decimal.

```php
<?php

$result = 0.1 + 0.2;
$expected = 0.3;

if ($result === 0.3) { /* never executes */ }

$epsilon = 0.00001;
if (abs($result - $expected) < $epsilon) {
    echo "Equal (with tolerance)";
}
```

### `bool` (booleano)

```php
<?php

$enabled = true;
$disabled = false;

echo true;   // "1"
echo false;  // "" (empty string)

var_dump(true);  // bool(true)
var_dump(false); // bool(false)
```

Valores que o PHP trata como `false` em contexto booleano (falsy):

```php
<?php

$falsy = [
    false,      // boolean false
    0,          // integer zero
    0.0,        // float zero
    -0,         // negative integer zero
    -0.0,       // negative float zero
    '',         // empty string
    '0',        // string "0"
    [],         // empty array
    null,       // null
    // SimpleXML objects created from empty tags
    // (specific internal instances)
];

foreach ($falsy as $value) {
    echo var_export($value, true) . ' → ' . var_export((bool)$value, true) . "\n";
}
```

### `string` (cadeia de caracteres)

```php
<?php

// 4 formas de declarar strings:
$s1 = 'Single quotes';       // No interpolation, no escapes (except \\ and \')
$s2 = "Double quotes";       // Interpolates variables, interprets escapes
$s3 = <<<EOT
Heredoc: works like double quotes
EOT;
$s4 = <<<'EOT'
Nowdoc: works like single quotes
EOT;
```

#### Aspas simples vs aspas duplas

**Convenção da comunidade**: use aspas simples como padrão. Só recorra a
aspas duplas quando precisar de interpolação ou escapes (`\n`, `\t`, etc.).
Quando você vê aspas duplas, já sabe que tem algo dinâmico ali.

```php
<?php

$name = "Charles";

echo 'Hello, $name!\n';

echo "Hello, $name!\n";
```

#### Interpolação de strings

```php
<?php

$fruit = "apple";
$quantity = 5;

echo "I have $quantity $fruit(s).";

echo "I have {$quantity} {$fruit}(s).";

$product = ['name' => 'Pen', 'price' => 2.50];
echo "Product: {$product['name']} costs \$ {$product['price']}";

class Item {
    public string $name = 'Notebook';
}
$item = new Item();
echo "Item: {$item->name}";

// Expressions are NOT allowed in interpolation
$total = $quantity * 2.50;
echo "Total: {$total}";

// Or use concatenation
echo "Total: " . ($quantity * 2.50);
```

#### Heredoc

Sintaxe para strings **multilinha** que funciona como **aspas duplas**:
interpola variáveis e interpreta escapes (`\n`, `\t`).

A ideia é evitar concatenação infinita ou escape de aspas quando você tem
um bloco grande de texto (HTML, SQL, templates). O identificador de
abertura (`<<<HTML`) e fechamento podem ser qualquer nome; a linha do
fechamento **não pode ter nada além do nome e ponto-e-vírgula**.

```php
<?php

$name = "World";
$version = PHP_VERSION;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Hello, {$name}!</title>
</head>
<body>
    <h1>Welcome, {$name}</h1>
    <p>Running PHP {$version}</p>
</body>
</html>
HTML;

echo $html;
```

A partir do PHP 7.3, o fechador **pode ser indentado**. O nível de
indentação do fechador define quantos espaços/tabs são removidos do
início de cada linha do conteúdo:

```php
<?php

function generateEmail(): string
{
    $user = "John";

    return <<<TEMPLATE
        Hello, {$user}!

        Your order has been confirmed.

        Sincerely,
        Team
        TEMPLATE;
}

echo generateEmail();
```

> ⚠️ **Cuidado**: nada pode aparecer na linha do fechador além do nome dele.
> Nem espaço, nem comentário. `HTML; // comment` **quebra**.

#### Nowdoc

É o Heredoc, só que funciona como **aspas simples**: **nada** é
interpretado. Nem variável, nem escape. O identificador de abertura
precisa estar entre aspas simples (`<<<'TEXT'`).

Útil quando o texto contém `$` literal (JavaScript, shell scripts,
expressões regulares) e você não quer que o PHP confunda com variável.

```php
<?php

$name = "Mary";

$text = <<<'TEXT'
Hello, $name!
No variable interpolation here.
No escapes either: \n \t
TEXT;

echo $text;
// Output: Hello, $name!
//         No variable interpolation here.
//         No escapes either: \n \t
```

---

## Declarações de tipo (Type Declarations)

A partir do PHP 7.0+, podemos declarar tipos para parâmetros de funções, retornos e propriedades.

### Tipos escalares em funções

```php
<?php

function add(int $a, int $b): int
{
    return $a + $b;
}

echo add(5, 3);
echo add(5, "3");
// add(5, "abc");  // TypeError if strict_types=1
```

### Tipos em propriedades (PHP 7.4+ / 8.0+)

```php
<?php

class Product
{
    public string $name;
    public float $price;
    public int $stock;
    public bool $available;

    public function __construct(
        string $name,
        float $price,
        int $stock = 0
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->available = $stock > 0;
    }
}
```

### Constructor property promotion (PHP 8.0+)

```php
<?php

class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $stock = 0,
        public bool $available = false,
    ) {
        $this->available = $stock > 0;
    }
}
```

---

## Tipos union e intersection

### Union types (PHP 8.0+)

Um parâmetro ou retorno pode aceitar múltiplos tipos:

```php
<?php

function formatId(int|string $id): string
{
    return (string) $id;
}

echo formatId(42);     // "42"
echo formatId("ABC");  // "ABC"

// Retorno com union type
function findUser(string $id): ?array
{
    // ?array is syntactic sugar for array|null
    $users = [
        '1' => ['name' => 'Alice'],
        '2' => ['name' => 'Bob'],
    ];
    return $users[$id] ?? null;
}

// Union com mais de 2 tipos
function process(mixed $value): int|float|string  // mixed = any type (see 'mixed' section below)
{
    return match (true) {
        is_int($value)   => $value * 2,
        is_float($value) => round($value, 2),
        is_string($value) => strtoupper($value),
        default          => throw new \InvalidArgumentException('Invalid type'),
    };
}
```

> ⚠️ **Cuidado**: O tipo `false` pode fazer parte de um union type (`int|false`),
> útil para funções que retornam `false` em caso de erro (padrão antigo do PHP).
> Use com cautela — exceções ou `null` são melhores.

### Intersection types (PHP 8.1+)

Exige que o valor satisfaça **todos** os tipos ao mesmo tempo:

```php
<?php

interface HasName
{
    public function getName(): string;
}

interface HasPrice
{
    public function getPrice(): float;
}

class Product implements HasName, HasPrice
{
    public function __construct(
        private string $name,
        private float $price,
    ) {}

    public function getName(): string { return $this->name; }
    public function getPrice(): float { return $this->price; }
}

class Service implements HasName, HasPrice
{
    public function __construct(
        private string $name,
        private float $hourlyRate,
        private int $hours,
    ) {}

    public function getName(): string { return $this->name; }
    public function getPrice(): float { return $this->hourlyRate * $this->hours; }
}

// Accepts ANY object that implements BOTH interfaces
function displayPrice(HasName& HasPrice $item): string
{
    return "{$item->getName()}: \$ " . number_format($item->getPrice(), 2, '.', ',');
}

$product = new Product('Keyboard', 250.00);
$service = new Service('Consulting', 150.00, 3);

echo displayPrice($product);
echo displayPrice($service);
```

### Tipos nullable (`?Tipo`)

```php
<?php

function findById(int $id): ?string
{
    // ?string is equivalent to string|null
    if ($id === 0) {
        return null;
    }
    return "Record #{$id}";
}

$result = findById(0);
var_dump($result);

$result = findById(42);
var_dump($result);
```

---

## Tipos especiais modernos

### `mixed` (PHP 8.0+)

`mixed` equivale a: `array|bool|callable|int|float|null|object|resource|string`
— ou seja, **qualquer coisa**. Antes do PHP 8.0, você simplesmente omitia o tipo
e o parâmetro/retorno era `mixed` implicitamente. O tipo explícito deixa sua
intenção clara: "sei que pode vir qualquer coisa, não foi esquecimento".

Regras importantes:

- **Já inclui `null`**. `mixed` sozinho aceita `null`. `?mixed` não faz
  sentido e é redundante.
- **Não participa de union type**. `mixed|int` é inválido — se já aceita tudo,
  não faz sentido restringir.
- **Herança**: você pode *estreitar* `mixed`. Um parâmetro `mixed` no pai
  pode virar `string` no filho (contravariância). Um retorno `mixed` no pai
  pode virar `int` no filho (covariância).

```php
<?php

function debug(mixed $value): void
{
    var_dump($value);
}

debug(42);
debug("text");
debug([1, 2, 3]);
debug(null);
debug(new stdClass());

// Return type mixed — useful for generic helpers
function safeGet(array $data, string $key, mixed $default = null): mixed
{
    return $data[$key] ?? $default;
}
```

### `void` (PHP 7.1+)

Indica que a função **não retorna nada**:

```php
<?php

function logMessage(string $msg): void
{
    error_log($msg);
    return;
}
```

### `never` (PHP 8.1+)

Indica que a função **nunca retorna**: ou lança exceção, ou chama `exit()`/`die()`:

```php
<?php

function redirect(string $url): never
{
    header("Location: {$url}");
    exit();
}

function fatalError(string $message): never
{
    throw new \RuntimeException($message);
}

function invalidType(): never
{
    // PHP entende que nunca chega depois disso
}
```

### `false` e `null` como tipos standalone (PHP 8.1+ - `false`, PHP 8.2+ - `null`)

```php
<?php
// false as standalone type (PHP 8.1+)

// Useful for functions that return false as failure indicator
function strpos_fake(string $haystack, string $needle): int|false
{
    $pos = strpos($haystack, $needle);
    return $pos; // int ou false
}

// null como tipo standalone (PHP 8.2+)
function getConfig(string $key): string|null
{
    // string|null instead of ?string (equivalent)
    $config = ['app' => 'MyApp'];
    return $config[$key] ?? null;
}
```

### `true` como tipo (PHP 8.2+)

```php
<?php

// PHP 8.2+ allows true as a type (useful in union types)
interface Validatable
{
    public function validate(): true|string;
    // Returns true if valid, or error message string
}

class Email implements Validatable
{
    public function __construct(private string $value) {}

    public function validate(): true|string
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return "Email '{$this->value}' is invalid";
        }
        return true;
    }
}
```

---

## `strict_types`

Por padrão, o PHP faz **coerção de tipos** (type juggling). Para forçar tipos estritos, use:

```php
<?php

declare(strict_types=1);

function add(int $first, int $second): int
{
    return $first + $second;
}

echo add(5, 3);
echo add(5, "3");     // TypeError! "3" is not int
```

```php
<?php

// Without strict_types (default): coercion happens
function sum(int $first, int $second): int
{
    return $first + $second;
}

echo sum(5, "3");      // 8 — string "3" coerced to int 3
echo sum(5, "3.7");    // 8 — float 3.7 → int 3 (precision loss!)
```

> 💡 **Dica**: **SEMPRE** use `declare(strict_types=1);` no topo de cada arquivo.
> Isso evita bugs silenciosos e torna o código mais previsível.

---

## Funções de inspeção de tipo

```php
<?php

$value = 42;

echo gettype($value);
echo get_debug_type($value);      // "int" (PHP 8.0+, more precise)

// Boolean checks
var_dump(is_int($value));         // bool(true)
var_dump(is_float($value));       // bool(false)
var_dump(is_string($value));      // bool(false)
var_dump(is_bool($value));        // bool(false)
var_dump(is_array($value));       // bool(false)
var_dump(is_object($value));      // bool(false)
var_dump(is_null($value));        // bool(false)
var_dump(is_numeric($value));
var_dump(is_scalar($value));
var_dump(is_callable($value));
var_dump(is_iterable($value));
var_dump(isset($value));
var_dump(empty($value));

// isset vs empty vs is_null
$var = null;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));

$var = 0;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));

$var = false;
var_dump(isset($var));
var_dump(empty($var));
var_dump(is_null($var));
```

### `settype()`

```php
<?php

$value = "123";
settype($value, "int");
echo $value;
var_dump($value);

$value = (int) "123";
```

---

## Arrays — Introdução

Em PHP, array é uma estrutura só que serve pra tudo: lista, dicionário,
fila, pilha, conjunto. Por baixo é um **ordered map** (hash map ordenado
por inserção). A distinção entre "indexado" e "associativo" é só o tipo
da chave — a estrutura é a mesma.

### Arrays indexados

Chaves são **inteiros sequenciais** começando do 0. Equivalente a listas
em Python ou arrays em JS:

```php
<?php

$fruits = ['apple', 'banana', 'orange', 'grape'];
// Internamente é: [0 => 'apple', 1 => 'banana', 2 => 'orange', 3 => 'grape']

echo $fruits[0];        // apple
echo $fruits[2];        // orange

// Append: PHP acha a maior chave int + 1
$fruits[] = 'strawberry';  // Vira índice 4
echo $fruits[4];            // strawberry
```

### Arrays associativos

Chaves são **strings** (ou podem ser mixadas com ints). Equivalente a
dicts em Python ou objetos em JS usados como map:

```php
<?php

$user = [
    'name'      => 'Anna',
    'email'     => 'anna@example.com',
    'age'       => 28,
    'isAdmin'   => true,
];

echo $user['name'];   // Anna
echo $user['email'];  // anna@example.com
```

### Arrays multidimensionais

Nada de especial — é só array dentro de array:

```php
<?php

$products = [
    [
        'name'  => 'Notebook',
        'price' => 3500.00,
        'tags'  => ['electronics', 'computing'],
    ],
    [
        'name'  => 'Mouse',
        'price' => 89.90,
        'tags'  => ['peripherals'],
    ],
];
```

Veremos arrays em profundidade em um módulo dedicado mais adiante.

---

## Enums (PHP 8.1+)

Enums permitem definir um conjunto fixo de valores possíveis.

### Enum puro (Pure Enum)

Diferente de C ou Java, os cases **não têm valor numérico implícito**. Se você
não declarar `: int` ou `: string`, cada case é só um objeto singleton com a
propriedade `->name`. Nada de `0, 1, 2, 3` automático.

```php
<?php

enum OrderStatus
{
    case Pending;
    case Paid;
    case Shipped;
    case Delivered;
    case Cancelled;
}

$status = OrderStatus::Paid;

echo $status->name;          // "Paid" (string)
var_dump($status);           // enum(OrderStatus::Paid)
var_dump(OrderStatus::cases()); // array com os 5 objetos

// $status->value  NÃO EXISTE — é pure enum, não tem backing value
// (int) $status   NÃO FUNCIONA — não dá pra converter pra int
```

### Backed Enum (com valor)

```php
<?php

enum ShirtSize: string
{
    case PP = 'pp';
    case P  = 'p';
    case M  = 'm';
    case G  = 'g';
    case GG = 'gg';
    case XG = 'xg';
}

function selectSize(ShirtSize $size): void
{
    echo "Size: " . $size->value . "\n";
}

selectSize(ShirtSize::M);

// A partir de um valor:
$size = ShirtSize::from('g');
echo $size->name;

// from() throws ValueError if value doesn't exist:
// ShirtSize::from('xxl'); // ValueError

// tryFrom() returns null if not found:
$attempt = ShirtSize::tryFrom('xxl');
var_dump($attempt);
```

### Enums com inteiros

```php
<?php

enum ErrorCode: int
{
    case NotFound           = 404;
    case Unauthorized       = 401;
    case InternalError      = 500;
    case ValidationFailed   = 422;
}

echo ErrorCode::NotFound->value; // 404
```

### Métodos em enums

```php
<?php

enum PaymentMethod: string
{
    case CreditCard  = 'credit_card';
    case BankSlip    = 'bank_slip';
    case Pix         = 'pix';
    case Debit       = 'debit_card';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard  => 'Credit Card',
            self::BankSlip    => 'Bank Slip',
            self::Pix         => 'PIX',
            self::Debit       => 'Debit Card',
        };
    }

    public function processingDeadline(): string
    {
        return match ($this) {
            self::Pix         => 'Instant',
            self::CreditCard  => 'Up to 24h',
            self::Debit       => 'Up to 24h',
            self::BankSlip    => 'Up to 3 business days',
        };
    }
}

$method = PaymentMethod::Pix;
echo $method->label();
echo $method->processingDeadline();

// Iterate over all cases:
foreach (PaymentMethod::cases() as $case) {
    echo "{$case->label()} → {$case->value}\n";
}
```

### Enum com interface e trait

> 💡 **Trait** é um mixin — código reutilizável injetado com `use`. Não é
> herança (`instanceof` não funciona), não gera relação de tipo. PHP não
> tem herança múltipla de classes, então traits são o escape pra
> compartilhar comportamento horizontalmente.

```php
<?php

interface Describable
{
    public function describe(): string;
}

trait DefaultDescription
{
    public function describe(): string
    {
        return match (true) {
            $this instanceof OrderStatus => "Order {$this->name}",
            default => $this->name,
        };
    }
}

enum OrderStatusWithDescription: string implements Describable
{
    use DefaultDescription;

    case Pending  = 'P';
    case Paid     = 'PG';
    case Shipped  = 'E';
    case Delivered = 'ET';
}

$status = OrderStatusWithDescription::Pending;
echo $status->describe();
```

---

## Type juggling (coerção de tipos)

O PHP converte tipos conforme o contexto:

```php
<?php

$result = "10" + 5;
echo $result;
var_dump($result);

$text = "Total: " . 100;
echo $text;
var_dump($text);

$sum = "1.5" + "2.5";
var_dump($sum);

$integerValue   = (int) "42";
$floatValue     = (float) "3.14";
$stringValue    = (string) 100;
$booleanValue   = (bool) 1;
$arrayValue     = (array) "test";
$objectValue    = (object) ['a' => 1];
```

```php
<?php

var_dump((bool) "");
var_dump((bool) "0");
var_dump((bool) "00");
var_dump((bool) "false");
var_dump((bool) []);
var_dump((bool) [0]);
var_dump((bool) 0);
var_dump((bool) 0.0);
var_dump((bool) -1);
var_dump((bool) null);
```

---

## 📚 Referências

- **Tipos**: [php.net/manual/pt_BR/language.types.php](https://www.php.net/manual/pt_BR/language.types.php)
- **Type declarations**: [php.net/manual/pt_BR/language.types.declarations.php](https://www.php.net/manual/pt_BR/language.types.declarations.php)
- **Type juggling**: [php.net/manual/pt_BR/language.types.type-juggling.php](https://www.php.net/manual/pt_BR/language.types.type-juggling.php)
- **Enums**: [php.net/manual/pt_BR/language.enumerations.php](https://www.php.net/manual/pt_BR/language.enumerations.php)
- **Strings**: [php.net/manual/pt_BR/language.types.string.php](https://www.php.net/manual/pt_BR/language.types.string.php)
- **Arrays**: [php.net/manual/pt_BR/language.types.array.php](https://www.php.net/manual/pt_BR/language.types.array.php)
- **Booleans**: [php.net/manual/pt_BR/language.types.boolean.php](https://www.php.net/manual/pt_BR/language.types.boolean.php)
- **Floats**: [php.net/manual/pt_BR/language.types.float.php](https://www.php.net/manual/pt_BR/language.types.float.php)
- **Integers**: [php.net/manual/pt_BR/language.types.integer.php](https://www.php.net/manual/pt_BR/language.types.integer.php)
- **Declarações de tipo no PHP 8**: [stitcher.io/blog/new-in-php-8](https://stitcher.io/blog/new-in-php-8)

---

## Próximo módulo

[→ 04 — Operadores](./04-operadores.md)
