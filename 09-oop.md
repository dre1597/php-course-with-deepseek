# 09 — Programação Orientada a Objetos em PHP (Parte 1)

## Índice

1. [Classes e Objetos](#classes-e-objetos)
2. [Propriedades](#propriedades)
3. [Métodos](#métodos)
4. [Constructor e Constructor Promotion](#constructor-e-constructor-promotion)
5. [Herança](#herança)
6. [Interfaces](#interfaces)
7. [Traits](#traits)
8. [Classes Abstratas](#classes-abstratas)
9. [Propriedades e Métodos Estáticos](#propriedades-e-métodos-estáticos)
10. [Constantes de Classe](#constantes-de-classe)
11. [Readonly: Propriedades e Classes](#readonly-propriedades-e-classes)
12. [Clonagem de Objetos](#clonagem-de-objetos)
13. [Property Hooks (PHP 8.4+)](#property-hooks-php-84)
14. [Asymmetric Visibility (PHP 8.4+)](#asymmetric-visibility-php-84)
15. [Final Property com Constructor Promotion (PHP 8.5+)](#final-property-com-constructor-promotion-php-85)
16. [Métodos Mágicos](#métodos-mágicos)
17. [Late Static Binding](#late-static-binding)
18. [Autoloading Básico](#autoloading-básico)
19. [Referências](#referências)

---

## Classes e Objetos

Uma **classe** é o molde (blueprint) que define propriedades e comportamentos. Um **objeto** é uma instância concreta da classe, criada com o operador `new`:

```php
<?php

class Product
{
    public string $name;
    public float $price;
}

$product1 = new Product();
$product1->name  = 'Notebook';
$product1->price = 3500.00;

$product2 = new Product();
$product2->name  = 'Mouse';
$product2->price = 89.90;

echo "{$product1->name}: R$ {$product1->price}"; // Notebook: R$ 3500
```

### `$this` — Referência ao Objeto Atual

Dentro da classe, `$this` referencia a instância atual:

```php
<?php

class Message
{
    public string $text;

    public function display(): void
    {
        echo $this->text;
    }
}

$message = new Message();
$message->text = 'Olá, mundo!';
$message->display(); // Olá, mundo!
```

---

## Propriedades

Propriedades armazenam o estado de um objeto. Os modificadores de visibilidade controlam o acesso:

| Modificador | Acesso |
|-------------|--------|
| `public` | Dentro e fora da classe, e em subclasses |
| `protected` | Dentro da classe e em subclasses |
| `private` | Apenas dentro da classe |

```php
<?php

class BankAccount
{
    private string $holder;
    private float $balance = 0.0;
    protected string $type = 'corrente';

    public function __construct(string $holder)
    {
        $this->holder = $holder;
    }

    public function deposit(float $value): void
    {
        if ($value > 0) {
            $this->balance += $value;
        }
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getHolder(): string
    {
        return $this->holder;
    }
}

$account = new BankAccount('João Silva');
$account->deposit(1000);
echo $account->getBalance();   // 1000

// $account->balance = 9999;   // Erro: propriedade private
// $account->type = 'poupanca'; // Erro: propriedade protected
```

### Typed Properties (PHP 7.4+)

Propriedades tipadas são inicializadas com `null` por padrão (se nullable) ou devem ser inicializadas antes de acessadas:

```php
<?php

class User
{
    public string $name;
    public int $age;
    public ?string $phone = null;  // nullable, com valor default
    public bool $active = true;
}

$user = new User();
$user->name  = 'Maria';
$user->age = 28;

// $u->name = 123; // TypeError — esperava string

// Se tentarmos acessar sem inicializar (propriedade nao-nullable):
// $u2 = new User();
// echo $u2->name; // Erro: propriedade nao inicializada
```

💡 **Dica:** Sempre inicialize propriedades tipadas no constructor ou com valores default. Propriedades não inicializadas de tipos não-nullable causam `Error` fatal quando acessadas.

---

## Métodos

Métodos definem comportamentos. Seguem a mesma lógica de visibilidade das propriedades:

```php
<?php

class Calculator
{
    public function sum(int $first, int $second): int
    {
        return $first + $second;
    }

    protected function validateOperation(string $operation): bool
    {
        return in_array($operation, ['+', '-', '*', '/']);
    }

    private function log(string $message): void
    {
        error_log("[Calculator] {$message}");
    }

    public function execute(string $operation, int $first, int $second): int|float
    {
        if (!$this->validateOperation($operation)) {
            throw new InvalidArgumentException("Operacao invalida: {$operation}");
        }

        $this->log("Executando {$first} {$operation} {$second}");

        return match ($operation) {
            '+' => $first + $second,
            '-' => $first - $second,
            '*' => $first * $second,
            '/' => $first / $second,
        };
    }
}

$calc = new Calculator();
echo $calc->execute('*', 6, 7); // 42
// $calc->validateOperation('*');   // Erro: metodo protected
// $calc->log('teste');           // Erro: metodo private
```

---

## Constructor e Constructor Promotion

### `__construct()`

O construtor roda ao instanciar a classe com `new`:

```php
<?php

class Book
{
    private string $title;
    private string $author;
    private int $year;

    public function __construct(string $title, string $author, int $year)
    {
        $this->title = $title;
        $this->author  = $author;
        $this->year    = $year;
    }

    public function getDescription(): string
    {
        return "{$this->title}, por {$this->author} ({$this->year})";
    }
}

$book = new Book('PHP Moderno', 'Ana Costa', 2026);
echo $book->getDescription(); // PHP Moderno, por Ana Costa (2026)
```

### Constructor Promotion (PHP 8.0+)

Sintaxe concisa que declara **e atribui** propriedades nos parâmetros do constructor:

```php
<?php

// Antes (PHP < 8.0):
class OldBook
{
    private string $title;
    private string $author;
    private int $year;

    public function __construct(string $title, string $author, int $year)
    {
        $this->title = $title;
        $this->author  = $author;
        $this->year    = $year;
    }
}

// Depois (PHP 8.0+):
class Book
{
    public function __construct(
        private string $title,
        private string $author,
        private int $year,
    ) {}

    public function getDescription(): string
    {
        return "{$this->title}, por {$this->author} ({$this->year})";
    }
}

$book = new Book('PHP Moderno', 'Ana Costa', 2026);
echo $book->getDescription(); // PHP Moderno, por Ana Costa (2026)

### Constructor Promotion com Valores Default

```php
<?php

class Configuration
{
    public function __construct(
        private string $host = 'localhost',
        private int $port = 3306,
        private string $user = 'root',
        private bool $debug = false,
    ) {}

    public function getDsn(): string
    {
        return "mysql:host={$this->host};port={$this->port}";
    }
}

$config = new Configuration(host: 'db.producao', debug: true);
echo $config->getDsn(); // mysql:host=db.producao;port=3306
```

### Corpo do Constructor com Promotion

Você pode ter lógica adicional no constructor mesmo usando promotion:

```php
<?php

class Order
{
    private DateTimeImmutable $createdAt;

    public function __construct(
        private string $id,
        private array $items,
        private float $total,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Order deve ter ao menos um item');
        }
    }
}
```

---

## Herança

Herança (`extends`) permite que uma classe filha reutilize e estenda propriedades e métodos da classe pai:

```php
<?php

class Vehicle
{
    public function __construct(
        protected string $brand,
        protected string $model,
        protected int $year,
    ) {}

    public function getDescription(): string
    {
        return "{$this->brand} {$this->model} ({$this->year})";
    }

    public function start(): string
    {
        return 'Vehicle ligado';
    }
}

class Car extends Vehicle
{
    public function __construct(
        string $brand,
        string $model,
        int $year,
        private int $doors = 4,
    ) {
        parent::__construct($brand, $model, $year);
    }

    #[\Override]
    public function start(): string
    {
        return 'Car ligado — vrum vrum!';
    }

    public function getInfoCompleta(): string
    {
        return parent::getDescription() . " — {$this->doors} portas";
    }
}

class Motorcycle extends Vehicle
{
    #[\Override]
    public function start(): string
    {
        return 'Motorcycle ligada — randandandan!';
    }
}

$car = new Car('Toyota', 'Corolla', 2026, 4);
echo $car->getInfoCompleta() . PHP_EOL; // Toyota Corolla (2026) — 4 portas
echo $car->start() . PHP_EOL;           // Car ligado — vrum vrum!

$motorcycle = new Motorcycle('Honda', 'CB500', 2025);
echo $motorcycle->start();                       // Motorcycle ligada — randandandan!
```

### `parent::`

A palavra-chave `parent::` acessa métodos e propriedades da classe pai:

```php
<?php

class LoggerBase
{
    protected function format(string $message): string
    {
        return date('[Y-m-d H:i:s] ') . $message;
    }
}

class LoggerArquivo extends LoggerBase
{
    #[\Override]
    protected function format(string $message): string
    {
        return parent::format($message) . ' [ARQUIVO]';
    }

    public function log(string $msg): void
    {
        echo $this->format($msg) . PHP_EOL;
    }
}

$logger = new LoggerArquivo();
$logger->log('Sistema iniciado');
// [2026-08-04 10:30:00] Sistema iniciado [ARQUIVO]
```

---

## Interfaces

Interfaces definem **contratos** — especificam quais métodos uma classe deve implementar, sem fornecer a implementação:

```php
<?php

interface Logavel
{
    public function getLogMessage(): string;
    public function getLogLevel(): string;
}

interface JsonSerializableCustom
{
    public function toJson(): string;
}

class Evento implements Logavel, JsonSerializableCustom
{
    public function __construct(
        private string $name,
        private array $dataPayload,
    ) {}

    public function getLogMessage(): string
    {
        return "Evento: {$this->name} — " . json_encode($this->dataPayload);
    }

    public function getLogLevel(): string
    {
        return 'info';
    }

    public function toJson(): string
    {
        return json_encode([
            'evento' => $this->name,
            'dados'  => $this->dataPayload,
        ], JSON_UNESCAPED_UNICODE);
    }
}

function registrar(Logavel $item): void
{
    echo "[{$item->getLogLevel()}] {$item->getLogMessage()}" . PHP_EOL;
}

$evento = new Evento('usuario.login', ['user_id' => 42, 'ip' => '192.168.1.1']);
registrar($evento); // [info] Evento: usuario.login — {"user_id":42,"ip":"192.168.1.1"}
echo $evento->toJson();
```

### Interface com Constantes (PHP 8.1+)

```php
<?php

interface Taxas
{
    public const float ICMS   = 0.18;
    public const float ISS    = 0.05;
    public const float PIS    = 0.0165;
    public const float COFINS = 0.076;
}

class NotaFiscal implements Taxas
{
    public function calculateImpostos(float $value): float
    {
        return $value * (Taxas::ICMS + Taxas::ISS + Taxas::PIS + Taxas::COFINS);
    }
}

$invoice = new NotaFiscal();
echo "Impostos: R$ " . $invoice->calculateImpostos(1000);
// Impostos: R$ 322.5
```

### Herança de Interfaces

```php
<?php

interface Contratual
{
    public function assinar(): void;
}

interface Renovavel extends Contratual
{
    public function renovar(): void;
}

class ContratoServico implements Renovavel
{
    public function assinar(): void
    {
        echo "Contrato assinado.\n";
    }

    public function renovar(): void
    {
        echo "Contrato renovado.\n";
    }
}
```

---

## Traits

Traits são mecanismos de **reutilização horizontal** de código. Permitem compartilhar métodos entre classes sem herança:

```php
<?php

trait Timestamps
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    private function initTimestamps(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

trait HasUuid
{
    private string $uuid;

    private function initUuid(): void
    {
        $this->uuid = bin2hex(random_bytes(16));
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}

class Post
{
    use Timestamps, HasUuid;

    public function __construct(
        private string $title,
        private string $content,
    ) {
        $this->initTimestamps();
        $this->initUuid();
    }

    public function edit(string $content): void
    {
        $this->content = $content;
        $this->touch();
    }
}

$post = new Post('PHP 8.5', 'Novas features...');
echo $post->getUuid() . PHP_EOL;          // ex: a1b2c3d4...
echo $post->getCriadoEm()->format('c');    // 2026-08-04T10:30:00+00:00
```

### Resolução de Conflitos em Traits

Se duas traits definem o mesmo método, use `insteadof`:

```php
<?php

trait LoggerJson
{
    public function formatLog(string $msg): string
    {
        return json_encode(['mensagem' => $msg]);
    }
}

trait LoggerTexto
{
    public function formatLog(string $msg): string
    {
        return "[LOG] {$msg}";
    }
}

class MeuLogger
{
    use LoggerJson, LoggerTexto {
        LoggerJson::formatLog insteadof LoggerTexto;  // usa o da LoggerJson
        LoggerTexto::formatLog as formatLogTexto;   // alias
    }
}

$logger = new MeuLogger();
echo $logger->formatLog('teste');      // {"mensagem":"teste"}
echo $logger->formatLogTexto('teste');  // [LOG] teste
```

### Traits com Métodos Abstratos

```php
<?php

trait Nameable
{
    abstract public function getName(): string;

    public function getDisplayName(): string
    {
        return mb_strtoupper($this->getName());
    }
}

class Category
{
    use Nameable;

    public function __construct(private string $name) {}

    public function getName(): string
    {
        return $this->name;
    }
}

echo (new Category('eletronicos'))->getDisplayName(); // ELETRONICOS
```

---

## Classes Abstratas

Classes abstratas (`abstract`) não podem ser instanciadas. Servem como base para outras classes, podendo conter métodos abstratos e implementados:

```php
<?php

abstract class Employee
{
    public function __construct(
        protected string $name,
        protected float $baseSalary,
    ) {}

    abstract public function calculateBonus(): float;

    public function getTotalSalary(): float
    {
        return $this->baseSalary + $this->calculateBonus();
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class Developer extends Employee
{
    public function calculateBonus(): float
    {
        return $this->baseSalary * 0.2; // 20%
    }
}

class Manager extends Employee
{
    public function calculateBonus(): float
    {
        return $this->baseSalary * 0.5; // 50%
    }
}

$dev = new Developer('Joao', 10000);
$manager = new Manager('Maria', 15000);

echo "{$dev->getName()}: R$ {$dev->getTotalSalary()}" . PHP_EOL; // Joao: R$ 12000
echo "{$manager->getName()}: R$ {$manager->getTotalSalary()}" . PHP_EOL; // Maria: R$ 22500

// $employee = new Employee('Teste', 1000); // Erro: classe abstrata
```

---

## Propriedades e Métodos Estáticos

Membros `static` pertencem à **classe**, não à instância. São acessados com `Classe::membro` ou `self::` / `static::` / `parent::`:

```php
<?php

class Counter
{
    private static int $total = 0;

    public function __construct()
    {
        self::$total++;
    }

    public static function getTotal(): int
    {
        return self::$total;
    }

    public static function reset(): void
    {
        self::$total = 0;
    }
}

new Counter();
new Counter();
new Counter();

echo Counter::getTotal(); // 3

Counter::reset();
echo Counter::getTotal(); // 0
```

### `self::` vs `static::`

- `self::` — resolve na **classe onde está definido** (early binding)
- `static::` — resolve na **classe chamadora** (late static binding, LSB)

```php
<?php

class Pai
{
    public static function quem(): string
    {
        return self::class;
    }

    public static function quemReal(): string
    {
        return static::class;
    }
}

class Filha extends Pai {}

echo Filha::quem();      // Pai   (self:: bind no compile time)
echo Filha::quemReal();  // Filha (static:: bind no runtime)
```

---

## Constantes de Classe

Constantes de classe são declaradas com `const` e acessadas via `Classe::CONSTANTE` ou `self::` / `static::`:

```php
<?php

class StatusOrder
{
    public const string PENDING     = 'pendente';
    public const string PROCESSING  = 'processando';
    public const string SHIPPED     = 'enviado';
    public const string DELIVERED   = 'entregue';
    public const string CANCELLED   = 'cancelado';

    private const array FINAL_STATUSES = [
        self::DELIVERED,
        self::CANCELLED,
    ];

    public static function isFinal(string $status): bool
    {
        return in_array($status, self::FINAL_STATUSES, true);
    }
}

$status = StatusOrder::PROCESSING;
var_dump(StatusOrder::isFinal($status)); // bool(false)
var_dump(StatusOrder::isFinal(StatusOrder::DELIVERED)); // bool(true)
```

### `final const` (PHP 8.1+)

Constantes `final` não podem ser sobrescritas em subclasses:

```php
<?php

class ConfigBase
{
    final public const string VERSAO = '1.0.0';
    public const string AMBIENTE = 'dev';
}

class ConfigProducao extends ConfigBase
{
    // public const string VERSAO = '2.0.0'; // Erro: nao pode sobrescrever final const
    public const string AMBIENTE = 'producao'; // OK
}
```

---

## Readonly: Propriedades e Classes

### Readonly Properties (PHP 8.1+)

Propriedades `readonly` só podem ser inicializadas **uma vez**, após o que se tornam imutáveis:

```php
<?php

class Client
{
    public function __construct(
        public readonly string $cpf,
        public readonly string $name,
        public readonly DateTimeImmutable $registrationDate = new DateTimeImmutable(),
    ) {}

    public function getAno(): int
    {
        return (int) $this->registrationDate->format('Y');
    }
}

$client = new Client('123.456.789-00', 'Maria Silva');
echo $client->name;      // Maria Silva
echo $client->getAno();  // 2026

// $client->name = 'Outra'; // Erro: propriedade readonly
```

Propriedades `readonly` podem ser inicializadas **apenas** no constructor ou na declaração. Após isso, tornam-se imutáveis.

### Readonly Classes (PHP 8.2+)

Se **todas** as propriedades de uma classe forem `readonly`, você pode marcar a classe inteira como `readonly`:

```php
<?php

readonly class Endereco
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $zipCode,
    ) {}
}

$end = new Endereco('Av. Paulista', 'Sao Paulo', 'SP', '01310-100');
echo "{$end->street}, {$end->city} - {$end->state}, {$end->zipCode}";
// Av. Paulista, Sao Paulo - SP, 01310-100

// $end->cidade = 'Rio'; // Erro: classe readonly
```

Classes `readonly`:
- Implicitamente tornam **todas** as propriedades tipadas como `readonly`
- Não podem ter propriedades `static`
- Não podem ter propriedades não tipadas
- Herança: uma classe `readonly` só pode ser herdada por outra classe `readonly`

⚠️ **Cuidado:** Propriedades `readonly` em classes normais não podem ser modificadas nem mesmo internamente após a inicialização. Classes `readonly` estendem essa restrição a todas as propriedades.

---

## Clonagem de Objetos

### `clone` — Cópia Superficial

`clone` cria uma cópia **superficial** (shallow copy) do objeto. Se houver propriedades que são referências a outros objetos, elas apontarão para o mesmo objeto:

```php
<?php

class Item
{
    public function __construct(public string $name) {}
}

class Cart
{
    public function __construct(
        public array $items = [],
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}
}

$c1 = new Cart(items: [new Item('Notebook')]);
$c2 = clone $c1;

$c2->items[0]->name = 'Mouse';

echo $c1->items[0]->name; // Mouse — modificado! (shallow copy)
```

### `__clone()` — Controle sobre a Clonagem

O método mágico `__clone()` é chamado **após** a cópia superficial, permitindo fazer deep copy ou ajustes:

```php
<?php

class Documento
{
    public function __construct(
        public string $title,
        public DateTimeImmutable $createdAt,
        public ?self $relacionado = null,
    ) {}

    public function __clone(): void
    {
        // Atualiza a data no clone
        $this->createdAt = new DateTimeImmutable();

        // Deep clone do objeto relacionado
        if ($this->relacionado !== null) {
            $this->relacionado = clone $this->relacionado;
        }
    }
}

$original = new Documento('Original', new DateTimeImmutable('2026-01-01'));
$copy = clone $original;

echo $original->createdAt->format('Y-m-d'); // 2026-01-01
echo $copy->createdAt->format('Y-m-d');    // 2026-08-04 (data atual)
```

### Clone com Array de Propriedades — PHP 8.5 NOVIDADE!

**PHP 8.5+** — O operador `clone` agora aceita um array associativo para **alterar propriedades durante a clonagem**. Isso elimina a necessidade de setters intermediários ou métodos `with*` apenas para ajustar um clone:

```php
<?php

class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock = 0,
    ) {}
}

$product = new Product('Notebook', 3500.00, 10);

// PHP 8.5+: clone com sobrescrita de propriedades
$discountedProduct = clone $product with ['price' => 2999.00];
$productOutOfStock  = clone $product with ['stock' => 0];

echo $product->price;                    // 3500 (original inalterado)
echo $discountedProduct->price;         // 2999
echo $productOutOfStock->stock;        // 0

// Clone com multiplas propriedades:
$newProduct = clone $product with [
    'name'    => 'Notebook Pro',
    'price'   => 4500.00,
    'stock'   => 20,
];
```

Isso funciona bem com propriedades `readonly`, onde você **não poderia** reatribuir após a criação. A sintaxe `clone ... with` resolve este problema de forma elegante e segura, pois não modifica o objeto original:

```php
<?php

readonly class Configuration
{
    public function __construct(
        public string $host,
        public int $port,
        public bool $debug = false,
    ) {}
}

$dev = new Configuration('localhost', 3306, debug: true);
$prod = clone $dev with ['host' => 'db.producao', 'debug' => false];

echo $dev->debug;  // true (inalterado)
echo $prod->debug; // false
```

💡 **Dica:** `clone ... with` substitui o padrão "wither" (métodos `withXxx()`) comum em objetos imutáveis. Em vez de escrever `withPreco()`, `withEstoque()`, etc., use `clone $obj with ['preco' => X]`.

---

## Property Hooks (PHP 8.4+)

**PHP 8.4+** — Property hooks permitem definir `get` e/ou `set` personalizados em propriedades, substituindo o comportamento padrão de leitura e escrita:

### Hook `get`

```php
<?php

class User
{
    public string $fullName {
        get => mb_convert_case($this->fullName, MB_CASE_TITLE, 'UTF-8');
    }

    public function __construct(string $name)
    {
        $this->fullName = $name;
    }
}

$user = new User('joao da silva');
echo $user->fullName; // Joao Da Silva
```

### Hook `set`

```php
<?php

class Product
{
    private float $rawPrice;

    public float $price {
        get => $this->rawPrice;
        set (float $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Preco nao pode ser negativo');
            }
            $this->rawPrice = round($value, 2);
        }
    }

    public function __construct(float $price)
    {
        $this->price = $price;
    }
}

$product = new Product(99.999);
echo $product->price; // 100

// $product->price = -10; // InvalidArgumentException
```

### Propriedade Somente Leitura (read-only virtual property)

```php
<?php

class Rectangle
{
    public function __construct(
        private float $width,
        private float $height,
    ) {}

    public float $area {
        get => $this->width * $this->height;
    } // sem set = somente leitura
}

$rectangle = new Rectangle(10, 5);
echo $rectangle->area; // 50

// $rectangle->area = 60; // Erro: propriedade nao tem set hook
```

### Propriedade Somente Escrita

```php
<?php

class Logger
{
    private array $messages = [];

    public string $message {
        set (string $value) {
            $this->messages[] = date('[H:i:s] ') . $value;
        }
    } // sem get = somente escrita

    public function getMessages(): array
    {
        return $this->messages;
    }
}

$logger = new Logger();
$logger->message = 'Sistema iniciado';
$logger->message = 'Processamento concluido';

print_r($logger->getMessages());
// [[10:30:00] Sistema iniciado, [10:30:05] Processamento concluido]

// echo $logger->message; // Erro: propriedade nao tem get hook
```

### Property Hooks em Interfaces

```php
<?php

interface Nameable
{
    public string $fullName { get; }
}

class Person implements Nameable
{
    public string $fullName {
        get => $this->firstName . ' ' . $this->lastName;
    }

    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}
```

💡 **Dica:** Property hooks substituem de forma limpa o que antes exigia métodos mágicos `__get()`/`__set()` ou propriedades privadas com getters/setters. Use-os para validação, transformação e propriedades computadas.

---

## Asymmetric Visibility (PHP 8.4+)

**PHP 8.4+** — Permite definir visibilidades **diferentes** para leitura (`get`) e escrita (`set`) de uma propriedade:

```php
<?php

class Report
{
    // Todos leem, mas apenas a classe modifica
    public private(set) string $title;

    // Todos leem, classe e subclasses modificam
    public protected(set) int $views = 0;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function incrementView(): void
    {
        $this->views++;
    }
}

class ReportPremium extends Report
{
    public function resetViews(): void
    {
        $this->views = 0; // OK — protected(set)
        // $this->title = 'novo';     // Erro — private(set) nao acessivel em subclasse
    }
}

$report = new Report('Vendas Q3');
echo $report->title;              // Vendas Q3 — public get

// $report->title = 'Q4';          // Erro — private(set)
// $report->views = 100;    // Erro — protected(set)
```

### Sintaxes Suportadas

| Declaração | Leitura | Escrita |
|------------|---------|---------|
| `public private(set) string $x` | pública | privada (só a classe) |
| `public protected(set) string $x` | pública | protegida (classe + subclasses) |
| `protected private(set) string $x` | protegida | privada (só a classe) |
| `protected protected(set) string $x` | protegida | protegida (equivalente a `protected`) |

### Casos de Uso

```php
<?php

// Exemplo 1: UUID gerado uma vez, visivel externamente
class Entidade
{
    public private(set) string $id;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(16));
    }
}

// Exemplo 2: Counter somente incrementavel internamente
class Visitante
{
    public protected(set) int $accessCount = 0;

    public function registrarAcesso(): void
    {
        $this->accessCount++;
    }
}

// Exemplo 3: Configuration que so pode ser alterada via metodo
class Conexao
{
    public private(set) string $host;
    public private(set) int $port;

    public function __construct(string $host, int $port)
    {
        $this->host  = $host;
        $this->port = $port;
    }

    public function reconectar(string $host, int $port): void
    {
        $this->fechar();
        $this->host  = $host;
        $this->port = $port;
    }

    private function fechar(): void { /* ... */ }
}
```

💡 **Dica:** Asymmetric visibility reduz boilerplate — você não precisa mais de propriedades `private` + métodos `get()` públicos só para expor um valor sem permitir escrita externa.

---

## Final Property com Constructor Promotion — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Agora é possível combinar `final` com constructor promotion, criando propriedades que **não podem ser sobrescritas em subclasses**:

```php
<?php

class Modelo
{
    public function __construct(
        final public string $tabela,
    ) {}
}

class User extends Modelo
{
    // public string $tabela = 'usuarios'; // Erro: propriedade final nao pode ser sobrescrita
}
```

Também funciona com `readonly`:

```php
<?php

abstract class EventoDominio
{
    public function __construct(
        final public readonly string $id,
        final public readonly DateTimeImmutable $ocorridoEm = new DateTimeImmutable(),
    ) {}
}

class UserCriado extends EventoDominio
{
    public function __construct(
        string $id,
        public readonly string $name,
    ) {
        parent::__construct($id);
    }

    // Nao pode sobrescrever $id nem $ocorridoEm
}
```

---

## Métodos Mágicos

Métodos mágicos são interceptores chamados pelo PHP em situações específicas. Todos começam com `__`:

### `__get($name)` — Acesso a Propriedade Inacessível

Chamado ao tentar **ler** uma propriedade que não existe ou é inacessível:

```php
<?php

class Container
{
    private array $dataPayload = [];

    public function __get(string $name): mixed
    {
        return $this->dataPayload[$name] ?? null;
    }
}

$container = new Container();
echo $container->qualquer_chave; // null (sem erro!)
```

### `__set($name, $valor)` — Escrita em Propriedade Inacessível

```php
<?php

class ConfigDinamica
{
    private array $values = [];

    public function __set(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function getAll(): array
    {
        return $this->values;
    }
}

$config = new ConfigDinamica();
$config->debug = true;
$config->host  = 'localhost';
$config->port = 3306;

print_r($config->getAll());
/*
Array
(
    [debug] => 1
    [host] => localhost
    [porta] => 3306
)
*/
```

### `__call($metodo, $args)` — Chamada a Método Inacessível

```php
<?php

class MicroOrm
{
    public function __call(string $method, array $args): mixed
    {
        if (str_starts_with($method, 'findBy')) {
            $column = lcfirst(substr($method, 6));
            return $this->findBy($column, $args[0]);
        }

        throw new BadMethodCallException("Metodo {$method} nao existe");
    }

    private function findBy(string $column, mixed $value): ?array
    {
        // simulacao de consulta
        echo "SELECT * FROM tabela WHERE {$column} = {$value}\n";
        return null;
    }
}

$orm = new MicroOrm();
$orm->findByNome('Maria');   // SELECT * FROM tabela WHERE nome = Maria
$orm->findByEmail('a@b.com'); // SELECT * FROM tabela WHERE email = a@b.com
```

### `__toString()` — Representação como String

```php
<?php

class Dinheiro
{
    public function __construct(
        private float $value,
        private string $currency = 'BRL',
    ) {}

    public function __toString(): string
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }
}

$price = new Dinheiro(199.9);
echo $price;              // R$ 199,90
echo "Preco: {$price}";  // Preco: R$ 199,90
```

### `__invoke()` — Objeto como Função

Faz com que um objeto possa ser chamado como se fosse uma função:

```php
<?php

class ValidadorEmail
{
    public function __invoke(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

$validator = new ValidadorEmail();

var_dump($validator('user@domain.com')); // bool(true)
var_dump($validator('invalid'));         // bool(false)

// Uso como callback:
$emails = ['a@b.com', 'invalido', 'c@d.com'];
$validos = array_filter($emails, $validator);
print_r($validos); // ['a@b.com', 'c@d.com']
```

### `__debugInfo()` — Controlar Saída do `var_dump()`

```php
<?php

class User
{
    public function __construct(
        private string $name,
        private string $password,
        private string $email,
    ) {}

    public function __debugInfo(): array
    {
        return [
            'nome'  => $this->name,
            'email' => $this->email,
            'senha' => '***REDACTED***',
        ];
    }
}

$u = new User('Maria', 'senha123', 'maria@email.com');
var_dump($u);
/*
object(User)#1 (3) {
  ["nome"]=> string(5) "Maria"
  ["email"]=> string(16) "maria@email.com"
  ["senha"]=> string(13) "***REDACTED***"
}
*/
```

💡 **Dica:** Use `__debugInfo()` para ocultar dados sensíveis (senhas, tokens) ao fazer debugging.

---

## Late Static Binding

Late Static Binding (`static::`) resolve a referência na **classe que fez a chamada** (runtime), não na classe onde o código está escrito (compile time). Essencial para hierarquias com métodos de fábrica:

```php
<?php

abstract class Repository
{
    protected static string $table;

    public static function find(int $id): ?static
    {
        $table = static::$table;  // LSB: resolve na subclasse
        echo "SELECT * FROM {$table} WHERE id = {$id}" . PHP_EOL;
        return $id > 0 ? new static() : null; // LSB: instancia a subclasse
    }

    public static function table(): string
    {
        return static::$table;
    }
}

class UserRepository extends Repository
{
    protected static string $table = 'usuarios';
}

class OrderRepository extends Repository
{
    protected static string $table = 'pedidos';
}

$user = UserRepository::find(1);
// SELECT * FROM usuarios WHERE id = 1

$order = OrderRepository::find(42);
// SELECT * FROM pedidos WHERE id = 42

echo UserRepository::table(); // usuarios
echo OrderRepository::table();  // pedidos
```

Sem `static::`, `self::$table` sempre retornaria o valor da classe `Repository` (que não está definido).

---

## Autoloading Básico

Autoloading carrega classes quando são referenciadas pela primeira vez, eliminando a necessidade de `require`/`include` manual.

### `spl_autoload_register()`

```php
<?php

// Configuration basica de autoloading
spl_autoload_register(function (string $classe): void {
    // Converte namespace em caminho de arquivo
    // Ex: App\Models\User -> src/Models/User.php
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $classe) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Agora qualquer classe no namespace App sera carregada:
// use App\Models\User;
// use App\Services\EmailService;
```

### Padrão PSR-4 (Recomendado)

Use o Composer para autoloading PSR-4. Arquivo `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Após rodar `composer dump-autoload`, todas as classes no diretório `src/` com namespace `App\` são carregadas. O Composer gera um mapa otimizado em `vendor/autoload.php`.

```php
<?php

// No entry point da aplicacao:
require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\HomeController;
use App\Models\User;

$controller = new HomeController();  // carregado
$user     = new User();        // carregado
```

---

## 📚 Referências

- [Documentação oficial: Classes e Objetos](https://www.php.net/manual/pt_BR/language.oop5.php)
- [Visibilidade](https://www.php.net/manual/pt_BR/language.oop5.visibility.php)
- [Construtores e Destrutores](https://www.php.net/manual/pt_BR/language.oop5.decon.php)
- [Constructor Promotion (PHP 8.0)](https://www.php.net/manual/pt_BR/language.oop5.decon.php#language.oop5.decon.constructor.promotion)
- [Herança](https://www.php.net/manual/pt_BR/language.oop5.inheritance.php)
- [Interfaces](https://www.php.net/manual/pt_BR/language.oop5.interfaces.php)
- [Traits](https://www.php.net/manual/pt_BR/language.oop5.traits.php)
- [Classes Abstratas](https://www.php.net/manual/pt_BR/language.oop5.abstract.php)
- [Métodos e Propriedades Estáticas](https://www.php.net/manual/pt_BR/language.oop5.static.php)
- [Constantes de Classe](https://www.php.net/manual/pt_BR/language.oop5.constants.php)
- [Readonly Properties (PHP 8.1)](https://www.php.net/manual/pt_BR/language.oop5.properties.php#language.oop5.properties.readonly-properties)
- [Readonly Classes (PHP 8.2)](https://www.php.net/manual/pt_BR/language.oop5.basic.php#language.oop5.basic.class.readonly)
- [Clonagem de Objetos](https://www.php.net/manual/pt_BR/language.oop5.cloning.php)
- [Clone with — PHP 8.5](https://wiki.php.net/rfc/clone_with)
- [Property Hooks (PHP 8.4)](https://www.php.net/manual/pt_BR/language.oop5.property-hooks.php)
- [Asymmetric Visibility (PHP 8.4)](https://www.php.net/manual/pt_BR/language.oop5.visibility.php#language.oop5.visibility.asymmetric)
- [Final Property com Constructor Promotion — PHP 8.5](https://wiki.php.net/rfc/final_properties)
- [Métodos Mágicos](https://www.php.net/manual/pt_BR/language.oop5.magic.php)
- [Late Static Binding](https://www.php.net/manual/pt_BR/language.oop5.late-static-bindings.php)
- [Autoloading com spl_autoload_register](https://www.php.net/manual/pt_BR/function.spl-autoload-register.php)
- [PSR-4: Autoloading Standard](https://www.php-fig.org/psr/psr-4/)
- [Atributo #[\Override]](https://www.php.net/manual/pt_BR/language.attributes.php#language.attributes.override)

---

> **Capítulo anterior:** [08 — Strings](08-strings.md)
> **Próximo capítulo:** [10 — Tratamento de Erros e Exceções](10-tratamento-de-erros.md)
