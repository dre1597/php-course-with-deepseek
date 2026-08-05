# 04 — Operadores

## Operadores Aritméticos

```php
<?php

$a = 10;
$b = 3;

echo $a + $b;   // 13
echo $a - $b;   // 7
echo $a * $b;   // 30
echo $a / $b;   // 3.3333333333333
echo $a % $b;   // 1
echo $a ** $b;  // 1000  (10³, PHP 5.6+)
```

### Divisão e módulo

```php
<?php

var_dump(10 / 2);   // int(5) — exact division within int range
var_dump(10 / 3);   // float(3.3333333333333)

// float modulo (PHP 8.0+)
var_dump(10.5 % 2.8);     // 2.1 → 10.5 - (3 * 2.8) = 10.5 - 8.4 = 2.1
var_dump(fmod(10.5, 2.8)); // 2.1 — fmod() for compatibility

echo 2 ** 8;    // 256
echo 2 ** 0;    // 1
echo 2 ** -1;   // 0.5 (1/2)
```

### Negação e identidade

```php
<?php

$a = 10;

echo -$a;   // -10  — negation
echo +$a;   // 10   — identity (no-op)
echo - -$a; // 10   — double negation = original value
```

---

## Operadores de Atribuição

```php
<?php

$a = 10;

$a += 5;     // $a = $a + 5    → 15
$a -= 3;     // $a = $a - 3    → 12
$a *= 2;     // $a = $a * 2    → 24
$a /= 4;     // $a = $a / 4    → 6
$a %= 4;     // $a = $a % 4    → 2
$a **= 3;    // $a = $a ** 3   → 8
$a .= "abc"; // $a = $a . "abc" → "8abc" (string concatenation)
```

### Atribuição em cadeia

```php
<?php

$a = $b = $c = 42;
echo $a; // 42
echo $b; // 42
echo $c; // 42

// Right-to-left evaluation
$i = 1;
$j = ($i += 5) * 2;  // $i becomes 6, $j = 6 * 2 = 12
echo "i={$i}, j={$j}"; // i=6, j=12
```

---

## Operadores de Comparação

```php
<?php

$a = 5;
$b = "5";
$c = 10;

var_dump($a == $b);  // bool(true) — loose equality (type coercion)
var_dump($a == $c);  // bool(false)

var_dump($a === $b); // bool(false) — strict equality (same value AND type)
var_dump($a === 5);  // bool(true)

var_dump($a != $b);  // bool(false)
var_dump($a <> $b);  // bool(false) — same as !=, less common

var_dump($a !== $b); // bool(true) — types differ
var_dump($a !== 5);  // bool(false)

var_dump($a < $c);   // bool(true)
var_dump($a > $c);   // bool(false)
var_dump($a <= 5);   // bool(true)
var_dump($a >= 6);   // bool(false)
```

### Tabela de coerção do `==`

```php
<?php

var_dump(0 == "0");          // true
var_dump(0 == "");           // true
var_dump(0 == "zero");       // false (PHP 8.0+: non-numeric string ≠ 0)
var_dump(0 == null);         // true
var_dump(0 == false);        // true
var_dump(0 == []);           // false (PHP 8.0+)
var_dump("0" == false);      // true
var_dump("0" == null);       // false
var_dump(null == false);     // true
var_dump("" == false);       // true
var_dump("" == null);        // true
var_dump([] == false);       // false (PHP 8.0+)
var_dump([] == null);        // true
var_dump([] == 0);           // false (PHP 8.0+)
var_dump(42 == true);        // true
var_dump(0 == false);        // true
var_dump(-1 == true);        // true
```

Use `===` and `!==` by default. `==` produces surprising results because of
PHP's type juggling. The handful of cases where `==` is what you want (comparing
user input against both `"0"` and `0`, for instance) don't justify making it
the habit.

### Operador Spaceship (`<=>`) — PHP 7.0+

Retorna `-1`, `0` ou `1` quando o operando da esquerda é menor, igual ou maior que o da direita.

```php
<?php

echo 1 <=> 1;   // 0
echo 1 <=> 2;   // -1
echo 2 <=> 1;   // 1

echo "a" <=> "b"; // -1 (string comparison, alphabetical order)
echo "b" <=> "a"; // 1
echo "a" <=> "a"; // 0

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numbers, fn($a, $b) => $a <=> $b);
print_r($numbers); // [1, 1, 2, 3, 4, 5, 6, 9]

$people = [
    ['name' => 'Alice',   'age' => 30],
    ['name' => 'Bob',     'age' => 25],
    ['name' => 'Charlie', 'age' => 30],
    ['name' => 'Diana',   'age' => 25],
];

usort($people, function(array $a, array $b): int {
    return $a['age'] <=> $b['age']
        ?: $a['name'] <=> $b['name'];
});

print_r($people);
/*
[
    ['name' => 'Bob',     'age' => 25],
    ['name' => 'Diana',   'age' => 25],
    ['name' => 'Alice',   'age' => 30],
    ['name' => 'Charlie', 'age' => 30],
]
*/
```

---

## Operadores Lógicos

```php
<?php

$active  = true;
$blocked = false;

var_dump($active && $blocked);  // false
var_dump($active and $blocked); // false (lower precedence than &&)

var_dump($active || $blocked);  // true
var_dump($active or $blocked);  // true (lower precedence than ||)

var_dump(!$active);             // false
var_dump(!$blocked);            // true

var_dump($active xor $blocked);  // true  — one true, one false
var_dump($active xor true);     // false — both true
var_dump(false xor false);      // false — both false
```

### Precedência: `&&` vs `and`, `||` vs `or`

O "bug" clássico do PHP. `&&` e `||` têm precedência **maior** que `=`.  
`and` e `or` têm precedência **menor** que `=`. Isso muda tudo:

```php
<?php

// && binds before =
$result = true && false;
var_dump($result); // bool(false) — parsed as $result = (true && false)

// and binds AFTER =
$result = true and false;
var_dump($result); // bool(true)! — parsed as ($result = true) and false

$result = false || true;
var_dump($result); // bool(true) — $result = (false || true)

$result = false or true;
var_dump($result); // bool(false) — ($result = false) or true
```

Prefira sempre `&&` e `||`. `and` e `or` são rasteira certa.

### Curto-circuito

```php
<?php

function checkA(): bool { echo "A "; return false; }
function checkB(): bool { echo "B "; return true; }

$result = checkA() && checkB(); // prints "A " — checkB() never runs
echo $result ? 'true' : 'false'; // false

echo "\n";

$result = checkB() || checkA(); // prints "B " — checkA() never runs
echo $result ? 'true' : 'false'; // true
```

```php
<?php

// Safe access via short-circuit
$config = null;
$dbHost = $config && $config['db'] && $config['db']['host'];
// Never throws, stops at the first false

$file = 'config.php';
$loaded = file_exists($file) && require $file;

// For defaults, prefer ?? over short-circuit
$name = $_GET['name'] ?? 'Guest';
```

---

## Operadores de Incremento/Decremento

```php
<?php

$count = 5;

echo ++$count;   // 6 — pre-increment
echo $count;     // 6

echo $count++;   // 6 — post-increment
echo $count;     // 7

echo --$count;   // 6
echo $count--;   // 6
echo $count;     // 5
```

```php
<?php

$i = 0;
while ($i++ < 5) {
    echo "post-increment: {$i}\n";
}
// 1, 2, 3, 4, 5

$i = 0;
while (++$i < 5) {
    echo "pre-increment: {$i}\n";
}
// 1, 2, 3, 4 (++$i becomes 5, 5 < 5 is false)
```

### Incremento de strings

```php
<?php

$char = 'A';
echo ++$char; // B
echo ++$char; // C
echo ++$char; // D

$char = 'Z';
echo ++$char; // AA
echo ++$char; // AB

$char = 'A99';
echo ++$char; // B00
```

Decremento em string (`$char--`) **não funciona** no PHP — só incremento.
Comparável ao `++` do Perl, mas sem `--`.

---

## Operadores de String

### Concatenação (`.`)

```php
<?php

$firstName = "Maria";
$lastName  = "Silva";

$fullName = $firstName . " " . $lastName;
echo $fullName; // Maria Silva

echo "Age: " . 30;                // Age: 30
echo "Price: $ " . 19.99;         // Price: $ 19.99
echo "Active: " . var_export(true); // Active: true
```

### Concatenação com atribuição (`.=`)

```php
<?php

$html = "<ul>\n";
$html .= "    <li>Item 1</li>\n";
$html .= "    <li>Item 2</li>\n";
$html .= "    <li>Item 3</li>\n";
$html .= "</ul>";

echo $html;
/*
<ul>
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
</ul>
*/
```

```php
<?php

$table   = "users";
$columns = ['name', 'email', 'age'];
$sql = "SELECT " . implode(', ', $columns) . " FROM {$table}";
$sql .= " WHERE active = 1";
$sql .= " ORDER BY name ASC";
$sql .= " LIMIT 10";

echo $sql;
// SELECT name, email, age FROM users WHERE active = 1 ORDER BY name ASC LIMIT 10
```

O exemplo acima é só concatenação. Para queries reais: prepared statements, sempre.
Nunca concatene input de usuário direto no SQL.

---

## Operador Ternário

```php
<?php

$age = 20;
$status = $age >= 18 ? "Adult" : "Minor";
echo $status; // Adult
```

### Ternário aninhado — evite

```php
<?php

$grade = 7.5;

// Works, but unreadable
$concept = $grade >= 9 ? 'A' : ($grade >= 7 ? 'B' : ($grade >= 5 ? 'C' : 'D'));

// Better: use match (PHP 8.0+)
$concept = match (true) {
    $grade >= 9 => 'A',
    $grade >= 7 => 'B',
    $grade >= 5 => 'C',
    default    => 'D',
};

echo $concept; // B
```

### Elvis operator (`?:`) — PHP 5.3+

```php
<?php

// If the first operand is truthy, use it; otherwise, use the second
$name = $_GET['name'] ?: 'Guest';
// Roughly equivalent to:
$name = $_GET['name'] ? $_GET['name'] : 'Guest';

$counter = 0;
$result = $counter ?: 10;
echo $result; // 10 — 0 is falsy
```

Se `0` for um valor válido na sua lógica, use `??` em vez de `?:`.

---

## Null Coalescing (`??`) — PHP 7.0+

```php
<?php

$name = $_GET['name'] ?? 'Guest';
// Uses $_GET['name'] if set and not null; otherwise 'Guest'

$config = ['db_host' => 'localhost', 'db_port' => null];

$host = $config['db_host'] ?? '127.0.0.1';
echo $host; // localhost — exists and not null

$port = $config['db_port'] ?? 3306;
echo $port; // 3306 — exists but is null, uses default

$user = $config['db_user'] ?? 'root';
echo $user; // root — key doesn't exist, uses default
```

### Encadeamento

```php
<?php

// PHP 7.4+: chain multiple ?? to try multiple sources
$name = $_GET['name'] ?? $_POST['name'] ?? $_COOKIE['name'] ?? 'Anonymous';
```

### `??=` (null coalescing assignment) — PHP 7.4+

```php
<?php

$name = 'John';
$name ??= 'Guest';
echo $name; // John — already has a value

unset($name);
$name ??= 'Guest';
echo $name; // Guest — was undefined

$config = [];
$config['host'] ??= 'localhost';
echo $config['host']; // localhost
```

### `??` vs `?:` vs ternário

```php
<?php

$value = 0;

echo $value ?: 'default';   // default  (?: checks truthiness: 0 is falsy)
echo $value ?? 'default';   // 0        (?? checks isset + not-null only)
echo $value ? $value : 'default'; // default
```

| Operator      | PHP   | Checks                | `$x = 0`   | `$x = null` |
|---------------|-------|------------------------|-----------|-------------|
| `?:` (Elvis)  | 5.3+  | truthy/falsy           | `'default'` | `'default'`  |
| `??`          | 7.0+  | isset + not-null       | `0`       | `'default'`  |
| `??=`         | 7.4+  | isset + not-null       | no-op     | assigns     |

---

## Nullsafe Operator (`?->`) — PHP 8.0+

```php
<?php

class Address
{
    public function __construct(
        public string $street,
        public ?City $city = null,
    ) {}
}

class City
{
    public function __construct(
        public string $name,
        public ?State $state = null,
    ) {}
}

class State
{
    public function __construct(
        public string $code,
    ) {}
}

$address = new Address(
    'Flower Street',
    new City('São Paulo', new State('SP'))
);

// Manual null check
$code = null;
if ($address->city !== null && $address->city->state !== null) {
    $code = $address->city->state->code;
}

// Nullsafe
$code = $address->city?->state?->code;
echo $code; // SP

$addressWithoutCity = new Address('Central Ave', null);
$code = $addressWithoutCity->city?->state?->code;
var_dump($code); // NULL — no error thrown
```

```php
<?php

class User
{
    public function getProfile(): ?Profile
    {
        return null;
    }
}

class Profile
{
    public function getAvatar(): string
    {
        return '/images/avatar.jpg';
    }
}

$user = new User();

// Without nullsafe
$avatar = $user->getProfile() !== null
    ? $user->getProfile()->getAvatar()
    : '/images/default.jpg';

// With nullsafe + ??
$avatar = $user->getProfile()?->getAvatar() ?? '/images/default.jpg';
echo $avatar; // /images/default.jpg
```

---

## Operador Pipe (`|>`) — PHP 8.5+

O pipe passa o valor da esquerda como argumento pra um **callable** na direita.
Dois formatos de callable no lado direito:

- **Função de 1 parâmetro**: use `nomeFuncao(...)` (first-class callable).
- **Função com múltiplos parâmetros**: envolva numa closure.

Arrow functions **precisam** de parênteses quando usadas com `|>`, ou dá erro
de sintaxe.

```php
<?php
// PHP 8.5+

// Without pipe: nested, hard to follow
$result = array_reverse(array_unique(array_map('strtoupper', $words)));

// With pipe: closures for multi-arg functions
$result = $words
    |> (fn($arr) => array_map(strtoupper(...), $arr))
    |> (fn($arr) => array_unique($arr))
    |> (fn($arr) => array_reverse($arr));
```

```php
<?php
// PHP 8.5+

$data = "  Alice,28,New York\n Bob,35,Chicago\n  Charlie,22,Boston  ";

$users = $data
    |> trim(...)                                          // 1-arg → first-class callable
    |> (fn($str) => explode("\n", $str))                  // 2-arg → closure
    |> (fn($lines) => array_map(trim(...), $lines))
    |> (fn($lines) => array_filter($lines, strlen(...)))
    |> (fn($lines) => array_map(
        fn(string $line): array => (
            sscanf($line, '%[^,],%d,%s')
            |> (fn($parts) => ['name' => $parts[0], 'age' => $parts[1], 'city' => $parts[2]])
        ),
        $lines,
    ));

print_r($users);
/*
[
    ['name' => 'Alice',   'age' => 28, 'city' => 'New York'],
    ['name' => 'Bob',     'age' => 35, 'city' => 'Chicago'],
    ['name' => 'Charlie', 'age' => 22, 'city' => 'Boston'],
]
*/
```

```php
<?php
// PHP 8.5+

function addTax(float $value, float $rate = 0.1): float
{
    return $value * (1 + $rate);
}

function applyDiscount(float $value, float $discount): float
{
    return $value * (1 - $discount);
}

function formatCurrency(float $value): string
{
    return '$' . number_format($value, 2);
}

$basePrice = 100.00;

$finalPrice = $basePrice
    |> (fn($v) => addTax($v, 0.15))
    |> (fn($v) => addTax($v, 0.05))
    |> (fn($v) => applyDiscount($v, 0.10))
    |> round(...)
    |> formatCurrency(...);

echo $finalPrice; // $108.68
```

```php
<?php
// PHP 8.5+

function validateEmail(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException("Invalid email: {$email}");
    }
    return $email;
}

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$input = ' John@Example.COM ';

try {
    $cleanEmail = $input
        |> sanitize(...)
        |> normalizeEmail(...)
        |> validateEmail(...);

    echo "Processed email: {$cleanEmail}"; // Processed email: john@example.com
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## Operadores de Array

### União (`+`)

```php
<?php

$defaults = [
    'host'     => 'localhost',
    'port'     => 3306,
    'charset'  => 'utf8mb4',
];

$userConfig = [
    'host'     => 'db.production.com',
    'username' => 'admin',
];

// Union: left-side keys take priority for duplicates
$config = $userConfig + $defaults;

print_r($config);
/*
[
    'host'     => 'db.production.com',  // from $userConfig (left side wins)
    'username' => 'admin',              // from $userConfig
    'port'     => 3306,                 // from $defaults (not in $userConfig)
    'charset'  => 'utf8mb4',            // from $defaults
]
*/
```

Não confunda `+` com `array_merge()`:
- `$a + $b`: chaves de `$a` têm prioridade
- `array_merge($a, $b)`: chaves string de `$b` sobrescrevem `$a`

```php
<?php

$a = [1, 2, 3];
$b = [4, 5, 6, 7];

print_r($a + $b);            // [1, 2, 3, 7] — indices 0,1,2 already in $a
print_r(array_merge($a, $b)); // [1, 2, 3, 4, 5, 6, 7] — concatenates everything
```

### Comparação de arrays

```php
<?php

$a = ['apple', 'banana'];
$b = [0 => 'apple', 1 => 'banana'];
$c = ['banana', 'apple'];

var_dump($a == $b);   // true — same key/value pairs
var_dump($a === $b);  // true — same order and types
var_dump($a == $c);   // false — different values at positions
var_dump($a != $c);   // true
```

---

## Operadores Bitwise

```php
<?php

$a = 0b1100;  // 12
$b = 0b1010;  // 10

printf("%b\n", $a & $b);   // 1000 → 8
printf("%b\n", $a | $b);   // 1110 → 14
printf("%b\n", $a ^ $b);   // 0110 → 6
printf("%b\n", ~$a);       // ...11110011 → -13 (watch sign)
printf("%b\n", $a << 1);   // 11000 → 24 (multiply by 2)
printf("%b\n", $a << 2);   // 110000 → 48 (multiply by 4)
printf("%b\n", $a >> 1);   // 110 → 6 (divide by 2)
printf("%b\n", $a >> 2);   // 11 → 3 (divide by 4)
```

### Permission flags (padrão comum)

```php
<?php

const CAN_READ    = 1;    // 0b0001
const CAN_WRITE   = 2;    // 0b0010
const CAN_DELETE  = 4;    // 0b0100
const CAN_ADMIN   = 8;    // 0b1000

$userPermissions  = CAN_READ | CAN_WRITE;                             // 3 (0b0011)
$adminPermissions = CAN_READ | CAN_WRITE | CAN_DELETE | CAN_ADMIN;    // 15 (0b1111)

if ($userPermissions & CAN_WRITE) {
    echo "User can write\n";
}

if (!($userPermissions & CAN_DELETE)) {
    echo "User cannot delete\n";
}

$userPermissions |= CAN_DELETE;   // add
$userPermissions &= ~CAN_DELETE;  // remove
$userPermissions ^= CAN_WRITE;    // toggle (flip on/off)
```

---

## Operador `instanceof`

```php
<?php

class Animal {}
class Dog extends Animal {}
interface CanFly {}

$dog = new Dog();

var_dump($dog instanceof Dog);     // true
var_dump($dog instanceof Animal);  // true — subclass
var_dump($dog instanceof CanFly);  // false — doesn't implement it

// PHP 8.0+ accepts class name as string
$class = 'Dog';
var_dump($dog instanceof $class); // true

$value = 42;
var_dump($value instanceof \DateTime); // false
$null = null;
var_dump($null instanceof \DateTime);  // false
```

---

## Tabela de precedência

Da maior para a menor precedência. Na dúvida, **use parênteses** — explícito
é melhor que memorizado.

| Precedência | Operadores                                           |
|-------------|------------------------------------------------------|
| 1           | `clone`, `new`                                       |
| 2           | `**`                                                 |
| 3           | `++`, `--`, `~`, `(int)`, `(float)`, `(string)`, etc |
| 4           | `instanceof`                                         |
| 5           | `!`                                                  |
| 6           | `*`, `/`, `%`                                        |
| 7           | `+`, `-`, `.`                                        |
| 8           | `<<`, `>>`                                           |
| 9           | `<`, `<=`, `>`, `>=`                                 |
| 10          | `==`, `!=`, `===`, `!==`, `<>`, `<=>`                |
| 11          | `&` (bitwise AND)                                    |
| 12          | `^` (bitwise XOR)                                    |
| 13          | `\|` (bitwise OR)                                    |
| 14          | `&&`                                                 |
| 15          | `\|\|`                                               |
| 16          | `??`                                                 |
| 17          | `?:`                                                 |
| 18          | `=`, `+=`, `-=`, `.=`, `??=`, etc                    |
| 19          | `and`                                                |
| 20          | `xor`                                                |
| 21          | `or`                                                 |
| 22          | `\|>` (pipe, PHP 8.5+)                               |

```php
<?php

// Sem parênteses: surpresa garantida
$result = true ? 'yes' : 'no' . ' thanks';
echo $result; // "yes" — ternary has lower precedence than concatenation

// Com parênteses
$result = (true ? 'yes' : 'no') . ' thanks';
echo $result; // "yes thanks"
```

---

## 📚 Referências

- [Operadores aritméticos](https://www.php.net/manual/en/language.operators.arithmetic.php)
- [Operadores de comparação](https://www.php.net/manual/en/language.operators.comparison.php)
- [Operadores lógicos](https://www.php.net/manual/en/language.operators.logical.php)
- [Operadores de string](https://www.php.net/manual/en/language.operators.string.php)
- [Operadores de array](https://www.php.net/manual/en/language.operators.array.php)
- [Operadores bitwise](https://www.php.net/manual/en/language.operators.bitwise.php)
- [Precedência de operadores](https://www.php.net/manual/en/language.operators.precedence.php)
- [Null coalescing (PHP 7.0)](https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.null-coalesce-op)
- [Nullsafe operator (PHP 8.0)](https://www.php.net/manual/en/migration80.new-features.php#migration80.new-features.nullsafe-operator)
- [Pipe Operator](https://www.php.net/manual/en/language.operators.functional.php)

---

## Próximo módulo

[→ 05 — Estruturas de Controle](./05-estruturas-de-controle.md)
