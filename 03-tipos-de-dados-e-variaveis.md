# 03 — Tipos de Dados e Variáveis

## Variáveis

### Declaração e uso

Toda variável em PHP começa com `$`:

```php
<?php

$name = "Maria";
$age = 30;
$height = 1.72;
$active = true;
```

Nomes de variáveis são **case-sensitive**:

```php
<?php

$name = "João";
$Nome = "Maria";
$NOME = "Carlos";

echo $name; // João
echo $Nome; // Maria
echo $NOME; // Carlos
// Três variáveis diferentes!
```

### Regras de nomenclatura

- Começam com `$` seguido de letra ou underscore (`_`)
- Depois podem conter letras, números e underscores
- **Não podem** começar com número
- Convenção: **camelCase** (`$meuNomeCompleto`)
- Unicode permitido (mas prefira ASCII por compatibilidade)

```php
<?php

// ✅ Válidos
$_variable = 1;
$userName = "ana";
$total2 = 100;
$coffee = "quente";          // Unicode — funciona, mas evite
$variable = "válido";
$ç = "çaracter especial";  // Funciona, mas péssima prática

// ❌ Inválidos
// $1lugar = "erro";        // Não pode começar com número
// $meu-nome = "erro";      // Hífen não é permitido
// $meu nome = "erro";      // Espaço não é permitido
```

### Atribuição por valor vs referência

```php
<?php

// Atribuição por valor (padrão)
$a = 10;
$b = $a;       // $b recebe uma CÓPIA do valor de $a
$a = 20;
echo $b;       // 10 (não foi afetado pela mudança em $a)
```

```php
<?php

// Atribuição por referência (&)
$a = 10;
$b = &$a;      // $b referencia o MESMO valor que $a
$a = 20;
echo $b;       // 20 (aponta pro mesmo lugar na memória)

$b = 30;
echo $a;       // 30 (as duas variáveis estão ligadas)
```

> ⚠️ **Cuidado**: Referências podem causar efeitos colaterais difíceis de depurar.
> Use apenas quando necessário.

### Variáveis variáveis

```php
<?php

$field = "name";
$$field = "Beatriz";   // Cria $name = "Beatriz"

echo $name;            // Beatriz
echo $$field;          // Beatriz
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

$a = 42;          // Decimal
$b = -17;         // Negativo
$c = 0b1010;      // Binário: 10 (prefixo 0b)
$d = 0o755;       // Octal: 493 (prefixo 0o, PHP 8.1+)
$e = 0xFF;        // Hexadecimal: 255 (prefixo 0x)
$f = 1_000_000;   // Separador numérico (PHP 7.4+): 1000000

echo PHP_INT_MAX;  // 9223372036854775807 (64 bits)
echo PHP_INT_MIN;  // -9223372036854775808
```

> 💡 **Dica**: Use `_` como separador de milhares para legibilidade:
> `$orcamento = 1_500_000;` no lugar de `$orcamento = 1500000;`

### `float` (ponto flutuante)

```php
<?php

$a = 1.5;
$b = -0.33;
$c = 1.2e3;       // 1200 (notação científica)
$d = 7E-10;       // 0.0000000007
$e = 0.1 + 0.2;   // ⚠️ 0.30000000000000004 (imprecisão IEEE 754)

echo PHP_FLOAT_MAX;  // 1.7976931348623E+308
echo PHP_FLOAT_MIN;  // 2.2250738585072E-308
```

> ⚠️ **Cuidado**: Operações com float podem ter imprecisão. Para cálculos
> financeiros, use strings e calcule em centavos, ou use a extensão BCMath,
> ou a extensão GMP, ou a extensão Decimal.

```php
<?php
// Comparando floats com segurança
$result = 0.1 + 0.2;
$expected  = 0.3;

// Jeito errado:
if ($result === 0.3) { /* nunca executa */ }

// Jeito certo: usar epsilon
$epsilon = 0.00001;
if (abs($result - $expected) < $epsilon) {
    echo "Iguais (com tolerância)";
}
```

### `bool` (booleano)

```php
<?php

$enabled = true;
$disabled = false;

echo true;   // "1"
echo false;  // "" (string vazia)

var_dump(true);  // bool(true)
var_dump(false); // bool(false)
```

Valores que o PHP trata como `false` em contexto booleano (falsy):

```php
<?php

// Todos estes são false quando convertidos para bool:
$falsy = [
    false,      // boolean false
    0,          // integer zero
    0.0,        // float zero
    -0,         // integer zero negativo
    -0.0,       // float zero negativo
    '',         // string vazia
    '0',        // string "0"
    [],         // array vazio
    null,       // null
    // SimpleXML objects criados de tags vazias
    // (instâncias internas específicas)
];

foreach ($falsy as $value) {
    echo var_export($value, true) . ' → ' . var_export((bool)$value, true) . "\n";
}
```

### `string` (cadeia de caracteres)

```php
<?php

// 4 formas de declarar strings:
$s1 = 'Aspas simples';        // Sem interpolação, sem escapes (exceto \\ e \')
$s2 = "Aspas duplas";         // Interpola variáveis, interpreta escapes
$s3 = <<<EOT
Heredoc: funciona como aspas duplas
EOT;
$s4 = <<<'EOT'
Nowdoc: funciona como aspas simples
EOT;
```

#### Aspas simples vs aspas duplas

```php
<?php

$name = "Carlos";

// Aspas simples: NÃO interpreta variáveis nem escapes especiais
echo 'Olá, $name!\n';  // Olá, $name!\n

// Aspas duplas: interpreta variáveis e escapes (\n, \t, \\, \$)
echo "Olá, $name!\n";  // Olá, Carlos! (com quebra de linha)
```

#### Interpolação de strings

```php
<?php

$fruit = "maçã";
$quantity = 5;

// Simples
echo "Eu tenho $quantity $fruit(s).";
// Eu tenho 5 maçã(s).

// Com chaves (recomendado para clareza)
echo "Eu tenho {$quantity} {$fruit}(s).";

// Acessando arrays e objetos
$product = ['name' => 'Caneta', 'price' => 2.50];
echo "Produto: {$product['name']} custa R\$ {$product['price']}";

class Item {
    public string $name = 'Caderno';
}
$item = new Item();
echo "Item: {$item->name}";

// Expressões dentro de chaves
echo "Total: {$quantity * 2.50}";  // PHP 8.1+ ???
```

#### Heredoc

```php
<?php

$name = "Mundo";
$versao = PHP_VERSION;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Olá, {$name}!</title>
</head>
<body>
    <h1>Bem-vindo, {$name}</h1>
    <p>Rodando PHP {$versao}</p>
</body>
</html>
HTML;

echo $html;
```

```php
<?php
// Heredoc com indentação flexível (PHP 7.3+)
// O marcador de fechamento pode ser indentado

function gerarEmail(): string
{
    $user = "João";

    return <<<TEMPLATE
        Olá, {$user}!

        Seu pedido foi confirmado.

        Atenciosamente,
        Equipe
        TEMPLATE;  // A indentação do fechador define a indentação do texto
}

echo gerarEmail();
```

#### Nowdoc

```php
<?php

// Nowdoc: como aspas simples — NADA é interpretado
$name = "Maria";

$text = <<<'TEXTO'
Olá, $name!
Aqui não há interpolação de variáveis.
Nem escapes: \n \t
TEXTO;

echo $text;
// Olá, $name!
// Aqui não há interpolação de variáveis.
// Nem escapes: \n \t
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

echo add(5, 3);    // 8
echo add(5, "3");  // 8 (coerção: string "3" → int 3)
// add(5, "abc");  // TypeError se strict_types=1
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
    // ?array é açúcar sintático para array|null
    $users = [
        '1' => ['name' => 'Alice'],
        '2' => ['name' => 'Bob'],
    ];
    return $users[$id] ?? null;
}

// Union com mais de 2 tipos
function process(mixed $value): int|float|string
{
    return match (true) {
        is_int($value)   => $value * 2,
        is_float($value) => round($value, 2),
        is_string($value) => strtoupper($value),
        default          => throw new \InvalidArgumentException('Tipo inválido'),
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

// A função aceita QUALQUER objeto que implemente AMBAS as interfaces
function displayPrice(HasName& HasPrice $item): string
{
    return "{$item->getName()}: R\$ " . number_format($item->getPrice(), 2, ',', '.');
}

$product = new Product('Teclado', 250.00);
$service = new Service('Consultoria', 150.00, 3);

echo displayPrice($product); // Teclado: R$ 250,00
echo displayPrice($service); // Consultoria: R$ 450,00
```

### Tipos nullable (`?Tipo`)

```php
<?php

function findById(int $id): ?string
{
    // ?string é equivalente a string|null
    if ($id === 0) {
        return null;
    }
    return "Registro #{$id}";
}

$result = findById(0);
var_dump($result); // NULL

$result = findById(42);
var_dump($result); // string(12) "Registro #42"
```

---

## Tipos especiais modernos

### `mixed` (PHP 8.0+)

Aceita qualquer tipo. É o tipo "coringa":

```php
<?php

function debug(mixed $value): void
{
    var_dump($value);
}

debug(42);
debug("texto");
debug([1, 2, 3]);
debug(null);
debug(new stdClass());
```

### `void` (PHP 7.1+)

Indica que a função **não retorna nada**:

```php
<?php

function logMessage(string $msg): void
{
    error_log($msg);
    // Não pode ter return com valor
    // return 1; // Erro!
    return; // OK: return vazio
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

// false como tipo standalone (PHP 8.1+)
// Útil para funções que retornam false como indicador de falha
function strpos_fake(string $haystack, string $needle): int|false
{
    $pos = strpos($haystack, $needle);
    return $pos; // int ou false
}

// null como tipo standalone (PHP 8.2+)
function getConfig(string $key): string|null
{
    // string|null ao invés de ?string (equivalente)
    $config = ['app' => 'MeuApp'];
    return $config[$key] ?? null;
}
```

### `true` como tipo (PHP 8.2+)

```php
<?php

// PHP 8.2+ permite true como tipo (útil em union types)
interface Validatable
{
    public function validate(): true|string;
    // Retorna true se válido, ou string com mensagem de erro
}

class Email implements Validatable
{
    public function __construct(private string $value) {}

    public function validate(): true|string
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return "Email '{$this->value}' inválido";
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

function add(int $a, int $b): int
{
    return $a + $b;
}

echo add(5, 3);     // 8 — OK
echo add(5, "3");   // TypeError! "3" não é int
```

```php
<?php

// Sem strict_types (padrão): coerção acontece
function add(int $a, int $b): int
{
    return $a + $b;
}

echo add(5, "3");   // 8 — a string "3" é convertida para int 3
echo add(5, "3.7"); // 8 — float 3.7 → int 3 (perda de precisão!)
```

> 💡 **Dica**: **SEMPRE** use `declare(strict_types=1);` no topo de cada arquivo.
> Isso evita bugs silenciosos e torna o código mais previsível.

---

## Funções de inspeção de tipo

```php
<?php

$value = 42;

// Obtém o tipo como string
echo gettype($value);             // "integer"
echo get_debug_type($value);      // "int" (PHP 8.0+, mais preciso)

// Verificações booleanas
var_dump(is_int($value));         // bool(true)
var_dump(is_float($value));       // bool(false)
var_dump(is_string($value));      // bool(false)
var_dump(is_bool($value));        // bool(false)
var_dump(is_array($value));       // bool(false)
var_dump(is_object($value));      // bool(false)
var_dump(is_null($value));        // bool(false)
var_dump(is_numeric($value));     // bool(true) — é número?
var_dump(is_scalar($value));      // bool(true) — é escalar?
var_dump(is_callable($value));    // bool(false)
var_dump(is_iterable($value));    // bool(false)
var_dump(isset($value));          // bool(true) — está definida e não é null?
var_dump(empty($value));          // bool(false) — é falsy?

// isset vs empty vs is_null
$var = null;
var_dump(isset($var));  // false — variável null é "não setada"
var_dump(empty($var));  // true  — null é "vazio"
var_dump(is_null($var));// true  — é null mesmo

$var = 0;
var_dump(isset($var));  // true  — está definida
var_dump(empty($var));  // true  — 0 é "vazio"
var_dump(is_null($var));// false — não é null

$var = false;
var_dump(isset($var));  // true
var_dump(empty($var));  // true  — false é "vazio"
var_dump(is_null($var));// false
```

### `settype()`

```php
<?php

$value = "123";
settype($value, "int");
echo $value;        // 123
var_dump($value);   // int(123)

// Equivalente ao cast explícito:
$value = (int) "123";
```

---

## Arrays — Introdução

### Arrays indexados

```php
<?php

// Sintaxe moderna (PHP 5.4+)
$fruits = ['maçã', 'banana', 'laranja', 'uva'];

// Sintaxe antiga (ainda válida)
$fruits = array('maçã', 'banana', 'laranja', 'uva');

echo $fruits[0];       // maçã
echo $fruits[2];       // laranja

$fruits[] = 'morango'; // Adiciona no final
echo $fruits[4];       // morango
```

### Arrays associativos

```php
<?php

$user = [
    'name'      => 'Ana Carolina',
    'email'     => 'ana@email.com',
    'age'       => 28,
    'isAdmin'   => true,
];

echo $user['name'];   // Ana Carolina
echo $user['email'];  // ana@email.com
```

### Arrays multidimensionais

```php
<?php

$products = [
    [
        'name'  => 'Notebook',
        'price' => 3500.00,
        'tags'  => ['eletrônicos', 'informática'],
    ],
    [
        'name'  => 'Mouse',
        'price' => 89.90,
        'tags'  => ['periféricos'],
    ],
];

echo $products[0]['name'];         // Notebook
echo $products[1]['tags'][0];      // periféricos
```

Veremos arrays em profundidade em um módulo dedicado mais adiante.

---

## Enums (PHP 8.1+)

Enums permitem definir um conjunto fixo de valores possíveis.

### Enum puro (Pure Enum)

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

function updateStatus(OrderStatus $status): void
{
    echo "Status alterado para: " . $status->name . "\n";
}

updateStatus(OrderStatus::Paid);      // Status alterado para: Pago
updateStatus(OrderStatus::Delivered);  // Status alterado para: Entregue
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
    echo "Tamanho: " . $size->value . "\n";
}

selectSize(ShirtSize::M); // Tamanho: m

// A partir de um valor:
$size = ShirtSize::from('g');
echo $size->name;  // G

// from() lança ValueError se o valor não existir:
// ShirtSize::from('xxl'); // ValueError

// tryFrom() retorna null se não existir:
$attempt = ShirtSize::tryFrom('xxl');
var_dump($attempt); // NULL
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
    case BankSlip    = 'boleto';
    case Pix         = 'pix';
    case Debit       = 'debit_card';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard  => 'Cartão de Crédito',
            self::BankSlip    => 'Boleto Bancário',
            self::Pix         => 'PIX',
            self::Debit       => 'Cartão de Débito',
        };
    }

    public function processingDeadline(): string
    {
        return match ($this) {
            self::Pix         => 'Instantâneo',
            self::CreditCard  => 'Até 24h',
            self::Debit       => 'Até 24h',
            self::BankSlip    => 'Até 3 dias úteis',
        };
    }
}

$method = PaymentMethod::Pix;
echo $method->label();              // PIX
echo $method->processingDeadline();  // Instantâneo

// Iterar sobre todos os cases:
foreach (PaymentMethod::cases() as $case) {
    echo "{$case->label()} → {$case->value}\n";
}
```

### Enum com interface e trait

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
            $this instanceof OrderStatus => "Pedido {$this->name}",
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
echo $status->describe();  // Pedido Pendente
```

---

## Type juggling (coerção de tipos)

O PHP converte tipos conforme o contexto:

```php
<?php

// String para int em contexto aritmético
$result = "10" + 5;
echo $result;          // 15
var_dump($result);     // int(15)

// Int para string em concatenação
$text = "Total: " . 100;
echo $text;              // Total: 100
var_dump($text);         // string(10) "Total: 100"

// String para float
$sum = "1.5" + "2.5";
var_dump($sum);          // float(4)

// Casts explícitos
$inteiro   = (int) "42";       // 42
$float     = (float) "3.14";   // 3.14
$string    = (string) 100;     // "100"
$boolean   = (bool) 1;         // true
$array     = (array) "teste";  // ["teste"]
$object    = (object) ['a' => 1];
```

```php
<?php
// Tabela de coerção para bool
var_dump((bool) "");         // false
var_dump((bool) "0");        // false
var_dump((bool) "00");       // true (string "00" != "0")
var_dump((bool) "false");    // true (string não vazia)
var_dump((bool) []);         // false
var_dump((bool) [0]);        // true (array não está vazio)
var_dump((bool) 0);          // false
var_dump((bool) 0.0);        // false
var_dump((bool) -1);         // true
var_dump((bool) null);       // false
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
