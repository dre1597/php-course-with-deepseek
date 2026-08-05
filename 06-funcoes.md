# 06 — Funções em PHP

## Índice

1. [Declaração de Funções](#declaração-de-funções)
2. [Parâmetros: Obrigatórios e Opcionais](#parâmetros-obrigatórios-e-opcionais)
3. [Parâmetros Nomeados (Named Arguments)](#parâmetros-nomeados-named-arguments)
4. [Type Declarations em Parâmetros e Retorno](#type-declarations-em-parâmetros-e-retorno)
5. [Union Types, Intersection Types, Mixed, Void, Never](#union-types-intersection-types-mixed-void-never)
6. [Retorno de Valores](#retorno-de-valores)
7. [Escopo de Variáveis](#escopo-de-variáveis)
8. [Funções Anônimas, Closures e Arrow Functions](#funções-anônimas-closures-e-arrow-functions)
9. [First-Class Callables (PHP 8.1+)](#first-class-callables-php-81)
10. [Funções Variádicas](#funções-variádicas)
11. [Funções por Referência](#funções-por-referência)
12. [Funções Recursivas](#funções-recursivas)
13. [Atributos: Override e NoDiscard](#atributos)
14. [Funções Built-In Mais Usadas](#funções-built-in-mais-usadas)
15. [Referências](#referências)

---

## Declaração de Funções

Em PHP, funções são declaradas com a palavra-chave `function`, seguida do nome da função, parênteses e corpo entre chaves. O nome da função deve começar com uma letra ou underscore, e não pode conter espaços.

```php
<?php

function greet(): void
{
    echo "Olá, mundo!";
}

greet(); // Olá, mundo!
```

A partir do PHP 8.0, funções podem ser declaradas em qualquer ordem — o PHP resolve o símbolo antes da execução. No entanto, funções condicionais (declaradas dentro de `if`) só ficam disponíveis após a condição ser avaliada como verdadeira.

### Nomes de Função

Nomes de função são case-insensitive:

```php
<?php

function myFunction(): void
{
    echo "executada";
}

MYFUNCTION(); // funciona, embora não seja recomendado
```

💡 **Dica:** Mantenha consistência no casing. O padrão PSR-12 recomenda `camelCase` para nomes de função.

---

## Parâmetros: Obrigatórios e Opcionais

Parâmetros são declarados entre os parênteses da função. Parâmetros com valor padrão (default) são opcionais e devem vir **depois** dos obrigatórios.

```php
<?php

function createUser(string $name, int $age = 18, bool $active = true): array
{
    return [
        'name'   => $name,
        'age'    => $age,
        'active' => $active,
    ];
}

// Chamadas válidas
$user1 = createUser('João');                  // idade=18, ativo=true
$user2 = createUser('Maria', 25);              // idade=25, ativo=true
$user3 = createUser('Pedro', 30, false);       // idade=30, ativo=false
```

⚠️ **Cuidado:** Não é possível declarar um parâmetro obrigatório após um opcional. O PHP emitirá um erro fatal:

```php
<?php

// ERRO: parâmetro obrigatório $second vem depois do opcional $first
function wrong(int $first = 1, int $second): void {}
```

### Valores Default com Expressões (PHP 8.1+)

Desde o PHP 8.1, valores padrão podem ser qualquer expressão escalar, incluindo `new`:

```php
<?php

function getData(DateTimeInterface $date = new DateTimeImmutable('now')): string
{
    return $date->format('Y-m-d');
}

echo getData(); // 2026-08-04 (data atual)
```

---

## Parâmetros Nomeados (Named Arguments)

**PHP 8.0+** — Você pode passar argumentos pelo nome do parâmetro, ignorando a ordem posicional:

```php
<?php

function createOrder(
    string $product,
    int $quantity = 1,
    float $price = 0.0,
    string $client = 'Anônimo',
): array {
    return compact('product', 'quantity', 'price', 'client');
}

// Chamadas com named arguments
$order1 = createOrder(
    product: 'Notebook',
    price: 3500.00,
    client: 'Ana',
    quantity: 2,
);

$order2 = createOrder(price: 99.90, product: 'Mouse');

print_r($order1);
/*
Array
(
    [product] => Notebook
    [quantity] => 2
    [price] => 3500
    [client] => Ana
)
*/
```

### Benefícios dos Named Arguments

- **Código mais legível** — para funções com muitos parâmetros booleanos ou opcionais.
- **Pular parâmetros opcionais** — você só passa o que precisa.
- **Independência de ordem** — menos risco de erro ao trocar posições de parâmetros do mesmo tipo.

⚠️ **Cuidado:** Named arguments expõem os nomes dos parâmetros como parte da API pública. Renomear um parâmetro quebra retrocompatibilidade se o código consumidor usar named arguments.

---

## Type Declarations em Parâmetros e Retorno

Type declarations definem os tipos esperados para parâmetros e para o valor de retorno. O PHP realiza coerção de tipos por padrão (`declare(strict_types=0)`). Com `declare(strict_types=1)`, a coerção é desabilitada e um `TypeError` é lançado em caso de incompatibilidade.

### Tipos Suportados

| Tipo | Descrição | Exemplo |
|------|-----------|---------|
| `int` | Número inteiro | `function soma(int $a, int $b): int` |
| `float` | Número de ponto flutuante | `function dividir(float $a, float $b): float` |
| `string` | Cadeia de caracteres | `function saudar(string $nome): string` |
| `bool` | Booleano | `function estaAtivo(bool $flag): bool` |
| `array` | Array | `function listar(array $itens): array` |
| `object` | Qualquer objeto | `function manipular(object $obj): object` |
| `callable` | Callback válido | `function executar(callable $fn): mixed` |
| `iterable` | Array ou Traversable | `function percorrer(iterable $it): void` |
| `null` | Valor nulo (só para nullable ou standalone com nullable types) | `function opcional(?string $x)` |
| `mixed` | Qualquer tipo | `function qualquer(mixed $valor): mixed` |
| `void` | Nenhum retorno | `function log(string $msg): void` |
| `never` | A função nunca retorna | `function abortar(): never` |
| `static` | Tipo da classe chamadora (retorno apenas) | `function instance(): static` |
| `false` / `true` | Tipos literais de retorno (PHP 8.2+) | `function isAdmin(): false` |
| `null` standalone | Independe de nullable (PHP 8.2+) | `function passiva(): null` |

### Exemplo com `strict_types`

```php
<?php

declare(strict_types=1);

function multiply(int $first, int $second): int
{
    return $first * $second;
}

echo multiply(3, 4);    // 12
// multiply(3.5, 4);    // TypeError — float não é aceito como int
```

### Nullable Types

Prefixar com `?` permite que o parâmetro ou retorno seja do tipo especificado ou `null`:

```php
<?php

function findById(int $id): ?array
{
    $data = [1 => ['name' => 'João'], 2 => ['name' => 'Maria']];
    return $data[$id] ?? null;
}

$result = findById(3); // null
var_dump($result);        // NULL
```

---

## Union Types, Intersection Types, Mixed, Void, Never

### Union Types (PHP 8.0+)

Permitem que um parâmetro ou retorno aceite mais de um tipo:

```php
<?php

function formatValue(int|float|string $value): string
{
    if (is_numeric($value)) {
        return number_format((float) $value, 2, ',', '.');
    }
    return strtoupper((string) $value);
}

echo formatValue(1500);      // 1.500,00
echo formatValue(99.9);      // 99,90
echo formatValue('abc');     // ABC
```

Union types não podem incluir `void`, `never`, `mixed`, `null` standalone, `true`, `false` ou tipos redundantes.

### Intersection Types (PHP 8.1+)

Usados apenas para **parâmetros** de funções/métodos, exigem que o valor satisfaça **todas** as interfaces ou classes especificadas:

```php
<?php

interface Logavel
{
    public function getLogMessage(): string;
}

interface Serializavel
{
    public function toArray(): array;
}

class Order implements Logavel, Serializavel
{
    public function getLogMessage(): string
    {
        return 'Order processed';
    }

    public function toArray(): array
    {
        return ['id' => 1];
    }
}

function registerAndSerialize(Logavel&Serializavel $entity): array
{
    echo $entity->getLogMessage() . PHP_EOL;
    return $entity->toArray();
}

$order = new Order();
print_r(registerAndSerialize($order));
```

### `mixed`

O tipo `mixed` indica que o valor pode ser de **qualquer tipo** — `null`, `bool`, `int`, `float`, `string`, `array` ou `object`. Introduzido no PHP 8.0 como tipo nativo:

```php
<?php

function process(mixed $input): mixed
{
    if (is_array($input)) {
        return array_map(strtoupper(...), $input);
    }
    if (is_string($input)) {
        return strtoupper($input);
    }
    return $input;
}

var_dump(process('hello'));        // string(5) "HELLO"
var_dump(process(['a', 'b']));     // array(2) { [0]=> "A", [1]=> "B" }
var_dump(process(42));             // int(42)
```

### `void`

Indica que a função **não retorna valor**. Qualquer tentativa de usar o retorno resulta em `null`, e declarar `return` com valor gera erro:

```php
<?php

function logMessage(string $msg): void
{
    error_log($msg);
    // return $msg;  // Erro: função void não pode retornar valor
}

$result = logMessage('teste');
var_dump($result); // NULL
```

### `never` (PHP 8.1+)

Indica que a função **nunca retorna** — ela sempre lança uma exceção, chama `exit()`/`die()`, ou entra em loop infinito:

```php
<?php

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

// abort(404, 'Página não encontrada');
```

⚠️ **Cuidado:** Se uma função declarada como `never` conseguir alcançar o fim do corpo sem lançar exceção ou interromper a execução, o PHP lançará um `TypeError`.

---

## Retorno de Valores

### `return` Simples

Toda função que não é `void` ou `never` deve retornar um valor compatível com seu type hint:

```php
<?php

function sum(int $first, int $second): int
{
    return $first + $second;
}
```

### Múltiplos Pontos de Retorno

É válido ter múltiplos `return` dentro de uma função:

```php
<?php

function classifyGrade(float $grade): string
{
    if ($grade >= 9.0) {
        return 'A';
    }
    if ($grade >= 7.0) {
        return 'B';
    }
    if ($grade >= 5.0) {
        return 'C';
    }
    return 'F';
}

echo classifyGrade(8.5); // B
```

### Retorno Condicional de Tipos (Union Types)

```php
<?php

function find(array $data, string $key): int|string|null
{
    if (array_key_exists($key, $data)) {
        return $data[$key];
    }
    return null;
}
```

---

## Escopo de Variáveis

### Escopo Local (Padrão)

Variáveis definidas dentro de uma função têm escopo **local** — não são acessíveis fora dela:

```php
<?php

function calculate(): void
{
    $localVar = 10;
    echo $localVar;
}

calculate();
// echo $localVar;         // Warning: Undefined variable $localVar
```

### Palavra-Chave `global`

A palavra-chave `global` importa uma variável do escopo global para dentro da função:

```php
<?php

$counter = 0;

function increment(): void
{
    global $counter;
    $counter++;
}

increment();
increment();
echo $counter; // 2
```

### Array Superglobal `$GLOBALS`

Alternativa ao `global`, o array `$GLOBALS` contém todas as variáveis do escopo global:

```php
<?php

$total = 100;

function applyDiscount(float $percentage): void
{
    $GLOBALS['total'] -= $GLOBALS['total'] * ($percentage / 100);
}

applyDiscount(10);
echo $total; // 90
```

### Cláusula `use` em Closures

Para funções anônimas (closures), usa-se `use` para herdar variáveis do escopo pai:

```php
<?php

$multiplier = 3;

$double = function (int $value) use ($multiplier): int {
    return $value * $multiplier;
};

echo $double(5); // 15
```

A herança por `use` é por valor. Para herdar por referência, prefixe com `&`:

```php
<?php

$accumulator = 0;

$add = function (int $value) use (&$accumulator): void {
    $accumulator += $value;
};

$add(10);
$add(5);
echo $accumulator; // 15
```

---

## Funções Anônimas, Closures e Arrow Functions

### Funções Anônimas (Closures)

São funções sem nome, atribuíveis a variáveis, passáveis como argumento e retornáveis:

```php
<?php

$greeting = function (string $name): string {
    return "Olá, {$name}!";
};

echo $greeting('Ana'); // Olá, Ana!
```

### Closures como Callbacks

```php
<?php

$names = ['João', 'Maria', 'Pedro'];

$mapped = array_map(function (string $name): string {
    return strtoupper($name);
}, $names);

print_r($mapped); // ['JOÃO', 'MARIA', 'PEDRO']
```

### Arrow Functions `fn =>` (PHP 7.4+)

Sintaxe concisa para closures de uma única expressão. Herdam variáveis do escopo (por valor):

```php
<?php

$multiplier = 2;
$values = [1, 2, 3, 4, 5];

$result = array_map(fn(int $number): int => $number * $multiplier, $values);

print_r($result); // [2, 4, 6, 8, 10]
```

Arrow functions também suportam type hints:

```php
<?php

$format = fn(int|float $value): string => number_format($value, 2, ',', '.');

echo $format(1234.5); // 1.234,50
```

💡 **Dica:** Use arrow functions para callbacks simples. Para lógica que requer múltiplas linhas ou statements (`if`, `foreach`), use closures tradicionais.

---

## First-Class Callables (PHP 8.1+)

A sintaxe `funcao(...)` cria uma referência **first-class callable** — um `Closure` a partir de qualquer função ou método existente:

```php
<?php

$upper = strtoupper(...);

echo $upper('php é incrível'); // PHP É INCRÍVEL

// Com array_map — antes (PHP < 8.1) vs depois
$names = ['ana', 'carlos', 'bia'];

// Antes:
$uppercase = array_map('strtoupper', $names);

// Agora (PHP 8.1+):
$uppercase = array_map(strtoupper(...), $names);

print_r($uppercase); // ['ANA', 'CARLOS', 'BIA']
```

### Funciona com Métodos de Instância e Estáticos

```php
<?php

class Calculator
{
    public function double(int $number): int
    {
        return $number * 2;
    }

    public static function triple(int $number): int
    {
        return $number * 3;
    }
}

$calc = new Calculator();

$doubleFn = $calc->double(...);          // método de instância
$tripleFn = Calculator::triple(...); // método estático

echo $doubleFn(10);     // 20
echo $tripleFn(10);  // 30
```

### `Closure::fromCallable()`

Converte qualquer `callable` em um objeto `Closure`:

```php
<?php

class Logger
{
    public function info(string $msg): void
    {
        echo "[INFO] {$msg}" . PHP_EOL;
    }
}

$logger = new Logger();
$logFn = Closure::fromCallable([$logger, 'info']);
// equivalente a: $logFn = $logger->info(...);

$logFn('Sistema iniciado'); // [INFO] Sistema iniciado
```

`Closure::fromCallable()` é útil quando você precisa garantir um `Closure`, não importa a forma do callable (string, array, invocável).

### `Closure::getCurrent()` — PHP 8.5 NOVIDADE!

**PHP 8.5+** — O método estático `Closure::getCurrent()` retorna o objeto `Closure` em execução. Isso é útil para **metaprogramação**, **depuração** e **reflection em tempo de execução**:

```php
<?php

$task = function (string $name): void {
    $current = Closure::getCurrent();
    // Reflection sobre a própria closure
    $ref = new ReflectionFunction($current);
    echo "Executando a closure '{$ref->getName()}' com parâmetro: {$name}" . PHP_EOL;
};

$task('importar_dados');
// Executando a closure '{closure}' com parâmetro: importar_dados
```

Outro caso de uso — criar callbacks que se auto-referenciam:

```php
<?php

$counter = function (int $step = 1) use (&$counter): void {
    static $value = 0;
    $value += $step;
    echo "Contador: {$value}" . PHP_EOL;

    if ($value < 10) {
        $current = Closure::getCurrent();
        $current($step);
    }
};

$counter(2);
// Contador: 2
// Contador: 4
// Contador: 6
// Contador: 8
// Contador: 10
```

💡 **Dica:** `Closure::getCurrent()` também pode ser usado para acessar o `$this` da closure atual em cenários avançados de binding.

---

## Funções Variádicas (`...$args`)

Permitem que uma função aceite um número variável de argumentos, que são empacotados em um array:

```php
<?php

function sumAll(int ...$numbers): int
{
    return array_sum($numbers);
}

echo sumAll(1, 2, 3);       // 6
echo sumAll(10, 20, 30, 40); // 100
echo sumAll();               // 0
```

### Combinando com Parâmetros Fixos

```php
<?php

function logWithContext(string $level, string $message, mixed ...$context): void
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    if (!empty($context)) {
        echo "Contexto: " . json_encode($context, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

logWithContext('ERROR', 'Falha na conexão', 'host' => 'db.local', 'porta' => 5432);
// [2026-08-04 10:30:00] [ERROR] Falha na conexão
// Contexto: {"host":"db.local","porta":5432}
```

### Desempacotamento de Argumentos

O operador spread também funciona ao **chamar** uma função, desempacotando um array em argumentos:

```php
<?php

function createRow(string $col1, string $col2, string $col3): string
{
    return "{$col1} | {$col2} | {$col3}";
}

$data = ['PHP', '8.5', '2026'];
echo createRow(...$data); // PHP | 8.5 | 2026
```

---

## Funções por Referência (`&$param`)

Prefixar um parâmetro com `&` faz com que a função possa modificar a variável original:

```php
<?php

function addSuffix(string &$text, string $suffix): void
{
    $text .= $suffix;
}

$name = 'PHP';
addSuffix($name, ' 8.5');
echo $name; // PHP 8.5
```

### Retorno por Referência

```php
<?php

$config = ['debug' => false, 'cache' => true];

function &getConfig(string $key): mixed
{
    global $config;
    return $config[$key];
}

$debug = &getConfig('debug');
$debug = true;

var_dump($config['debug']); // bool(true)
```

⚠️ **Cuidado:** Use referências com moderação. Elas reduzem a legibilidade e podem causar efeitos colaterais inesperados. Prefira retornar novos valores em vez de modificar os originais.

---

## Funções Recursivas

Uma função que chama a si mesma é **recursiva**. Toda recursão precisa de uma **condição de parada**:

```php
<?php

function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;                   // condição de parada
    }
    return $n * factorial($n - 1);    // chamada recursiva
}

echo factorial(5); // 120 (5 × 4 × 3 × 2 × 1)
```

### Recursão com Array (Percorrer Estrutura em Árvore)

```php
<?php

function listCategories(array $categories, int $level = 0): void
{
    foreach ($categories as $cat) {
        echo str_repeat('  ', $level) . "- {$cat['name']}" . PHP_EOL;
        if (!empty($cat['children'])) {
            listCategories($cat['children'], $level + 1);
        }
    }
}

$tree = [
    [
        'name'   => 'Eletrônicos',
        'children' => [
            ['name' => 'Celulares', 'children' => []],
            ['name' => 'Notebooks', 'children' => []],
        ],
    ],
    [
        'name'   => 'Livros',
        'children' => [
            ['name' => 'Ficção',    'children' => []],
            ['name' => 'Técnicos',  'children' => []],
            ['name' => 'Biografias', 'children' => []],
        ],
    ],
];

listCategories($tree);
/*
- Eletrônicos
  - Celulares
  - Notebooks
- Livros
  - Ficção
  - Técnicos
  - Biografias
*/
```

⚠️ **Cuidado:** Recursões muito profundas podem estourar o limite de memória ou o limite de recursão do PHP. O PHP tem um limite de recursão configurado via `xdebug.max_nesting_level` ou pelo próprio interpretador (~100000 níveis em PHP 8.x típico).

---

## Atributos

### `#[\Override]` (PHP 8.3+)

Marca um método que **sobrescreve** um método da classe pai. Se o método pai não existir, o PHP emite um erro fatal:

```php
<?php

class Animal
{
    public function makeSound(): string
    {
        return 'som genérico';
    }
}

class Dog extends Animal
{
    #[\Override]
    public function makeSound(): string
    {
        return 'au au';
    }
}

// Se a classe pai não tiver o método, #[\Override] causa erro fatal:
class Cat extends Animal
{
    // #[\Override]
    // public function meow(): string { return 'miau'; }
    // Erro: Cat::meow() não sobrescreve nenhum método
}
```

💡 **Dica:** Use `#[\Override]` sempre que sobrescrever métodos. Isso protege contra renomeações acidentais na classe pai e documenta a intenção.

### `#[\NoDiscard]` — PHP 8.5 NOVIDADE!

**PHP 8.5+** — O atributo `#[\NoDiscard]` indica que o valor de retorno de uma função **não deve ser ignorado**. Se o retorno for descartado, o PHP emitirá um `E_USER_NOTICE`:

```php
<?php

#[\NoDiscard]
function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

// Chamada correta:
$token = generateToken();

// Chamada incorreta — o retorno é descartado, dispara notice:
// generateToken();
// Notice: The return value of function generateToken() should not be discarded

// Também funciona em métodos:
class Database
{
    #[\NoDiscard]
    public function connect(): self
    {
        // lógica de conexão
        return $this;
    }
}

$db = new Database();
$db->connect(); // Notice: retorno descartado

// Correto:
$db = (new Database())->connect();
```

💡 **Dica:** `#[\NoDiscard]` é excelente para funções puras cujo retorno é o único propósito da chamada (ex: `array_map`, `strtoupper`), ou para métodos que retornam nova instância (fluent interfaces, imutáveis).

---

## Funções Built-In Mais Usadas (Overview)

O PHP possui milhares de funções nativas. Aqui estão algumas das mais frequentes:

### Strings

| Função | Descrição |
|--------|-----------|
| `strlen($s)` | Comprimento da string |
| `strpos($h, $n)` | Posição da primeira ocorrência de `$n` em `$h` |
| `str_replace($o, $n, $s)` | Substitui todas as ocorrências |
| `substr($s, $i, $len)` | Extrai substring |
| `trim($s)` | Remove espaços do início e fim |
| `explode($d, $s)` | Divide string em array |
| `implode($d, $a)` | Junta array em string |
| `str_contains($h, $n)` | Verifica se contém (PHP 8.0+) |
| `str_starts_with($h, $n)` | Verifica se começa com (PHP 8.0+) |
| `str_ends_with($h, $n)` | Verifica se termina com (PHP 8.0+) |

### Arrays

| Função | Descrição |
|--------|-----------|
| `count($a)` | Número de elementos |
| `in_array($v, $a)` | Verifica se valor existe no array |
| `array_key_exists($k, $a)` | Verifica se chave existe |
| `array_merge($a, $b)` | Mescla arrays |
| `array_map($fn, $a)` | Aplica função a cada elemento |
| `array_filter($a, $fn)` | Filtra elementos |
| `array_reduce($a, $fn)` | Reduz array a um valor |
| `array_values($a)` | Valores do array (reindexado) |
| `array_keys($a)` | Chaves do array |
| `sort($a)`, `rsort($a)` | Ordenação |

### Matemática

| Função | Descrição |
|--------|-----------|
| `abs($n)` | Valor absoluto |
| `round($n, $p)` | Arredondamento |
| `ceil($n)`, `floor($n)` | Teto e piso |
| `rand($min, $max)` | Número aleatório |
| `max(...$vals)`, `min(...$vals)` | Máximo e mínimo |

### Arquivos / Sistema

| Função | Descrição |
|--------|-----------|
| `file_get_contents($p)` | Lê arquivo inteiro |
| `file_put_contents($p, $d)` | Escreve em arquivo |
| `file_exists($p)` | Verifica existência |
| `is_dir($p)` | É diretório? |
| `mkdir($p)` | Cria diretório |
| `json_decode($j)` | Decodifica JSON |
| `json_encode($v)` | Codifica para JSON |

---

## 📚 Referências

- [Documentação oficial: Funções definidas pelo usuário](https://www.php.net/manual/pt_BR/functions.user-defined.php)
- [Argumentos de funções](https://www.php.net/manual/pt_BR/functions.arguments.php)
- [Named Arguments (PHP 8.0)](https://www.php.net/manual/pt_BR/functions.arguments.php#functions.named-arguments)
- [Tipos de declaração (Type Declarations)](https://www.php.net/manual/pt_BR/language.types.declarations.php)
- [Union Types](https://www.php.net/manual/pt_BR/language.types.type-system.php#language.types.type-system.composite.union)
- [Intersection Types](https://www.php.net/manual/pt_BR/language.types.type-system.php#language.types.type-system.composite.intersection)
- [Funções anônimas / Closures](https://www.php.net/manual/pt_BR/functions.anonymous.php)
- [Arrow Functions](https://www.php.net/manual/pt_BR/functions.arrow.php)
- [First-class callables](https://www.php.net/manual/pt_BR/functions.first_class_callable_syntax.php)
- [Closure::fromCallable()](https://www.php.net/manual/pt_BR/closure.fromcallable.php)
- [Closure::getCurrent() — PHP 8.5](https://www.php.net/manual/pt_BR/closure.getcurrent.php)
- [Funções variádicas](https://www.php.net/manual/pt_BR/functions.arguments.php#functions.variable-arg-list)
- [Atributo #[\Override]](https://www.php.net/manual/pt_BR/language.attributes.php#language.attributes.override)
- [Atributo #[\NoDiscard] — PHP 8.5](https://www.php.net/manual/pt_BR/language.attributes.php)
- [Escopo de variáveis](https://www.php.net/manual/pt_BR/language.variables.scope.php)
- [Alias de funções internas](https://www.php.net/manual/pt_BR/indexes.functions.php)
- [PHP 8.5 Release Notes (upcoming)](https://wiki.php.net/rfc)

---

> **Próximo capítulo:** [07 — Arrays](07-arrays.md)
