# 05 — Estruturas de Controle

## `if`, `else`, `elseif`

```php
<?php

$age = 20;

if ($age >= 18) {
    echo "Adult";
}
```

### `else`

```php
<?php

$temperature = 15;

if ($temperature >= 30) {
    echo "Very hot";
} else {
    echo "Pleasant temperature";
}
```

### `elseif` / `else if`

```php
<?php

$hour = 14;

if ($hour < 6) {
    echo "Early morning";
} elseif ($hour < 12) {
    echo "Morning";
} elseif ($hour < 18) {
    echo "Afternoon";
} elseif ($hour < 24) {
    echo "Night";
} else {
    echo "Invalid time";
}
```

`elseif` e `else if` são equivalentes em PHP; `elseif` (tudo junto) é mais idiomático.

### Condições com múltiplas expressões

```php
<?php

$isLoggedIn = true;
$hasPermission = false;

if ($isLoggedIn && $hasPermission) {
    echo "Access granted: admin panel";
} elseif ($isLoggedIn && !$hasPermission) {
    echo "Logged in but no admin permission. Redirecting...";
} else {
    echo "Please log in first";
}
```

### Input validation with `if`

```php
<?php

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$errors = [];

if ($email === '') {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
}

if ($password === '') {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($errors === []) {
    echo "All good! Proceeding...";
} else {
    foreach ($errors as $error) {
        echo "Error: {$error}\n";
    }
}
```

---

## `switch`

`switch` compara uma expressão com múltiplos valores possíveis:

```php
<?php

$weekDay = 3;

switch ($weekDay) {
    case 1:
        echo "Sunday";
        break;
    case 2:
        echo "Monday";
        break;
    case 3:
        echo "Tuesday";
        break;
    case 4:
        echo "Wednesday";
        break;
    case 5:
        echo "Thursday";
        break;
    case 6:
        echo "Friday";
        break;
    case 7:
        echo "Saturday";
        break;
    default:
        echo "Invalid day";
        break;
}
// Output: Tuesday
```

### Fall-through (without `break`)

Without `break`, execution "falls through" to the next `case`:

```php
<?php

$month = 2;
$year = 2024;

switch ($month) {
    case 1:
    case 3:
    case 5:
    case 7:
    case 8:
    case 10:
    case 12:
        echo "31 days";
        break;
    case 4:
    case 6:
    case 9:
    case 11:
        echo "30 days";
        break;
    case 2:
        // Leap year?
        $days = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0) ? 29 : 28;
        echo "{$days} days";
        break;
    default:
        echo "Invalid month";
}
// Output: 29 days (2024 is a leap year)
```

### Comparison in `switch`

`switch` uses **loose** comparison (`==`), not strict (`===`):

```php
<?php

$value = 0;

switch ($value) {
    case false:
        echo "Matched false"; // This runs! Because 0 == false
        break;
    case 0:
        echo "Matched 0";
        break;
}

// Watch out: switch uses ==, not ===
// "0" matches 0, false matches 0, null matches 0...
```

> **Cuidado**: `switch` usa comparação frouxa (`==`). `"0"` matcha com `0`, `false` matcha com `0`, `null` matcha com `0`... Para comparação estrita, prefira `match` (PHP 8.0+).

### `switch(true)` for complex conditions

```php
<?php

// Technique: switch(true) for complex conditions
$grade = 8.5;
$attendance = 85; // percentage
switch (true) {
    case $grade >= 7 && $attendance >= 75:
        echo "Approved";
        break;
    case $grade >= 5 && $attendance >= 75:
        echo "Recovery exam";
        break;
    case $attendance < 75:
        echo "Failed due to absences";
        break;
    default:
        echo "Failed by grade";
        break;
}
// Output: Approved
```

---

## `match` — PHP 8.0+

`match` é uma expressão que retorna valor, usa comparação estrita (`===`), e é mais concisa que `switch`:

### Vantagens sobre `switch`

| Característica          | `switch`                | `match`                                |
|-------------------------|-------------------------|----------------------------------------|
| Tipo de estrutura       | Statement (comando)     | Expressão (retorna valor)              |
| Comparação              | Frouxa (`==`)           | Estrita (`===`)                        |
| Fall-through            | Sim (precisa de `break`)| Não (apenas um braço executa)          |
| Retorno                 | Não retorna valor       | Retorna o valor do braço               |
| Exaustividade           | Não verificada           | Erro se nenhum braço corresponder      |
| Condições complexas     | Com `switch(true)`      | `match(true)` também funciona          |

### Sintaxe básica

```php
<?php

$statusCode = 404;

$message = match ($statusCode) {
    200     => 'OK',
    201     => 'Created',
    301     => 'Moved Permanently',
    404     => 'Not Found',
    500     => 'Internal Server Error',
    default => 'Unknown Status',
};

echo $message; // Not Found
```

### Múltiplas condições para o mesmo braço

```php
<?php

$day = 'saturday';

$dayType = match ($day) {
    'monday', 'tuesday', 'wednesday', 'thursday', 'friday' => 'Weekday',
    'saturday', 'sunday' => 'Weekend',
    default => 'Invalid day',
};

echo $dayType; // Weekend
```

### Expressions as branch bodies

```php
<?php

$value = 150.00;
$discountType = 'blackfriday';

$finalPrice = match ($discountType) {
    'vip'         => $value * 0.7,           // 30% off
    'blackfriday' => $value * 0.5,           // 50% off
    'subscriber'  => $value * 0.85,          // 15% off
    default       => $value,                 // full price
};

echo "Original price: R$ " . number_format($value, 2, ',', '.') . "\n";
echo "Final price: R$ " . number_format($finalPrice, 2, ',', '.') . "\n";
// Original price: R$ 150,00
// Final price: R$ 75,00
```

### `match` with enums

```php
<?php

enum PaymentMethod: string
{
    case Pix     = 'pix';
    case BankSlip  = 'bank_slip';
    case Credit = 'credit';
    case Debit  = 'debit';
}

function getProcessingFee(PaymentMethod $method): float
{
    return match ($method) {
        PaymentMethod::Pix     => 0.00,   // no fee
        PaymentMethod::BankSlip  => 3.50,   // flat fee
        PaymentMethod::Credit => 0.0499, // 4.99%
        PaymentMethod::Debit  => 0.0299, // 2.99%
    };
    // No default needed: enum covers all cases.
    // If a new case is added to the enum, PHP will throw
    // an UnhandledMatchError at runtime.
}

echo getProcessingFee(PaymentMethod::Pix); // 0.0
```

### `match(true)` for complex conditions

```php
<?php

$grade = 8.5;
$attendance = 85;

$result = match (true) {
    $grade >= 9 && $attendance >= 90  => 'Approved with honors',
    $grade >= 7 && $attendance >= 75  => 'Approved',
    $grade >= 5 && $attendance >= 75  => 'Recovery exam',
    $attendance < 75                 => 'Failed due to absences',
    default                          => 'Failed by grade',
};

echo $result; // Approved
```

### `match` without `default` and `UnhandledMatchError`

If no branch matches and there's no `default`, PHP throws `\UnhandledMatchError`:

```php
<?php

$direction = 'north';

try {
    $command = match ($direction) {
        'up'       => 'Move up',
        'down'     => 'Move down',
        'left'     => 'Move left',
        'right'    => 'Move right',
    };
} catch (\UnhandledMatchError $e) {
    echo "Direction '{$direction}' not recognized.";
}
// Direction 'north' not recognized.
```

---

## `while` e `do-while`

### `while`

Executa o bloco **enquanto** a condição for verdadeira. O PHP verifica a condição **antes** de cada iteração:

```php
<?php

$counter = 1;

while ($counter <= 5) {
    echo "Iteration {$counter}\n";
    $counter++;
}
// Iteration 1
// Iteration 2
// Iteration 3
// Iteration 4
// Iteration 5
```

```php
<?php

// Reading a CSV file line by line with while
$handle = fopen('data.csv', 'r');
if ($handle === false) {
    die('Could not open the file');
}

$lineNumber = 0;
while (($line = fgetcsv($handle)) !== false) {
    $lineNumber++;
    echo "Line {$lineNumber}: " . implode(' | ', $line) . "\n";
}
fclose($handle);
```

### `do-while`

A condição é verificada **depois** da execução. Garante que o bloco execute **pelo menos uma vez**:

```php
<?php

$attempts = 0;
$maxAttempts = 3;

do {
    $attempts++;
    echo "Attempt {$attempts} out of {$maxAttempts}\n";

    // Simulates an operation that might fail
    $success = random_int(0, 1) === 1;

    if ($success) {
        echo "Operation completed successfully!\n";
        break;
    }

    echo "Failed. ";
} while ($attempts < $maxAttempts);

if ($attempts === $maxAttempts && !$success) {
    echo "All attempts failed.\n";
}
```

---

## `for`

```php
<?php

// for (initialization; condition; increment)
for ($i = 0; $i < 5; $i++) {
    echo "i = {$i}\n";
}
// i = 0
// i = 1
// i = 2
// i = 3
// i = 4
```

### Multiple variables in `for`

```php
<?php

// Simultaneous increment and decrement
for ($i = 0, $j = 10; $i <= 10; $i++, $j--) {
    echo "i = {$i}, j = {$j}, sum = " . ($i + $j) . "\n";
}
```

### `for` with omitted expressions

```php
<?php

// No initialization (variable set outside)
$i = 0;
for (; $i < 5; $i++) {
    echo "{$i} ";
}
echo "\n";

// No increment (done inside the block)
for ($i = 0; $i < 5;) {
    echo "{$i} ";
    $i++;
}
echo "\n";

// Infinite loop with for (all expressions empty)
// for (;;) { ... } — equivalent to while(true) { ... }

// No condition (infinite loop — useful with internal break)
$i = 0;
for (;;) {
    if ($i >= 5) {
        break;
    }
    echo "{$i} ";
    $i++;
}
// 0 1 2 3 4
```

### Application: multiplication table

```php
<?php

echo "Multiplication table for 7:\n";
echo str_repeat('─', 20) . "\n";

for ($multiplier = 1; $multiplier <= 10; $multiplier++) {
    $result = 7 * $multiplier;
    echo "7 × " . str_pad($multiplier, 2, ' ', STR_PAD_LEFT) . " = " . str_pad($result, 2, ' ', STR_PAD_LEFT) . "\n";
}
/*
Multiplication table for 7:
────────────────────
7 ×  1 =  7
7 ×  2 = 14
7 ×  3 = 21
...
7 × 10 = 70
*/
```

### Application: HTML generation with `for`

```php
<?php

$months = [
    'January', 'February', 'March', 'April',
    'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December',
];

echo "<select name='mes'>\n";
for ($i = 0; $i < count($months); $i++) {
    $value = $i + 1;
    $selected = ($value === (int)date('m')) ? ' selected' : '';
    echo "    <option value='{$value}'{$selected}>{$months[$i]}</option>\n";
}
echo "</select>\n";
```

---

## `foreach`

Itera sobre arrays e objetos iteráveis:

### Values only

```php
<?php

$fruits = ['apple', 'banana', 'orange', 'grape', 'strawberry'];

foreach ($fruits as $fruit) {
    echo "Fruit: {$fruit}\n";
}
```

### Key and value

```php
<?php

$user = [
    'name'   => 'John Smith',
    'email'  => 'john@example.com',
    'age'    => 34,
    'city'   => 'New York',
];

foreach ($user as $key => $value) {
    echo ucfirst($key) . ": {$value}\n";
}
// Name: John Smith
// Email: john@example.com
// Age: 34
// City: New York
```

### `foreach` with nested arrays

```php
<?php

$products = [
    ['name' => 'Laptop',      'price' => 3500.00, 'stock' => 12],
    ['name' => 'Monitor 27"', 'price' => 1200.00, 'stock' => 5],
    ['name' => 'Keyboard',    'price' => 250.00,  'stock' => 30],
    ['name' => 'Mouse',       'price' => 120.00,  'stock' => 0],
];

echo "┌──────┬────────────────────┬──────────┬─────────┐\n";
echo "│ ID   │ Product            │ Price    │ Stock   │\n";
echo "├──────┼────────────────────┼──────────┼─────────┤\n";

foreach ($products as $id => $product) {
    $idStr     = str_pad($id + 1, 4, ' ', STR_PAD_LEFT);
    $nameStr   = str_pad($product['name'], 18, ' ');
    $priceStr  = 'R$ ' . str_pad(number_format($product['price'], 2, ',', '.'), 7, ' ', STR_PAD_LEFT);
    $stockStr  = str_pad($product['stock'], 7, ' ', STR_PAD_LEFT);

    $status = $product['stock'] > 0 ? '' : ' (out of stock)';

    echo "│ {$idStr} │ {$nameStr} │ {$priceStr} │ {$stockStr} │{$status}\n";
}

echo "└──────┴────────────────────┴──────────┴─────────┘\n";
```

### Modifying arrays with `foreach` (by reference)

```php
<?php

$numbers = [1, 2, 3, 4, 5];

// By value: does NOT modify the original array
foreach ($numbers as $num) {
    $num *= 2; // $num is a copy
}
print_r($numbers); // [1, 2, 3, 4, 5] — unchanged

// By reference: MODIFIES the original array
foreach ($numbers as &$num) {
    $num *= 2;
}
unset($num); // IMPORTANT: break the reference after the loop!
print_r($numbers); // [2, 4, 6, 8, 10]
```

> **Cuidado**: Sempre chame `unset()` na variável de referência após o loop.
> A última referência persiste e causa bugs sutis:
>
> ```php
> <?php
> $arr = [1, 2, 3];
> foreach ($arr as &$v) { $v *= 2; }
> // Oops, forgot unset($v)!
> foreach ($arr as $v) { echo $v; }
> // Last element gets overwritten unexpectedly!
> ```

### `foreach` with iterable objects

```php
<?php

// Any object implementing Traversable can be used with foreach

class Counter implements \Iterator
{
    private int $position = 0;
    private array $values;

    public function __construct(array $values)
    {
        $this->values = array_values($values);
    }

    public function current(): mixed { return $this->values[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { $this->position++; }
    public function rewind(): void { $this->position = 0; }
    public function valid(): bool { return isset($this->values[$this->position]); }
}

$counter = new Counter(['a', 'b', 'c']);
foreach ($counter as $index => $value) {
    echo "{$index}: {$value}\n";
}
```

### `foreach` with `$key => $value` on indexed arrays

```php
<?php

$colors = ['red', 'green', 'blue'];

foreach ($colors as $index => $color) {
    echo "[{$index}] = {$color}\n";
}
// [0] = red
// [1] = green
// [2] = blue
```

### `foreach` over strings (with `mb_str_split`)

```php
<?php

$text = "PHP";
$characters = mb_str_split($text); // PHP 7.4+

foreach ($characters as $i => $char) {
    echo "Position {$i}: {$char}\n";
}
// Position 0: P
// Position 1: H
// Position 2: P
```

---

## `break` e `continue`

### `break`

Stops the current loop immediately:

```php
<?php

echo "Searching for number 7...\n";

$numbers = [1, 3, 5, 7, 9, 11, 13];

foreach ($numbers as $position => $number) {
    echo "  Checking position {$position}: {$number}\n";
    if ($number === 7) {
        echo "  → Found at position {$position}!\n";
        break; // Stops the loop immediately
    }
}
/*
Searching for number 7...
  Checking position 0: 1
  Checking position 1: 3
  Checking position 2: 5
  Checking position 3: 7
  → Found at position 3!
*/
```

### `break N` (breaking multiple levels)

```php
<?php

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

$target = 5;
$found = false;

foreach ($matrix as $rowIndex => $row) {
    foreach ($row as $columnIndex => $value) {
        echo "  [{$rowIndex}][{$columnIndex}] = {$value}\n";
        if ($value === $target) {
            echo "  → Found at [{$rowIndex}][{$columnIndex}]\n";
            break 2; // Exits BOTH foreach loops
        }
    }
}
```

### `continue`

Skips to the next iteration of the loop:

```php
<?php

echo "Even numbers between 1 and 10:\n";

for ($i = 1; $i <= 10; $i++) {
    if ($i % 2 !== 0) {
        continue; // Skip odd numbers
    }
    echo "  {$i}\n";
}
```

### `continue N`

```php
<?php

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

foreach ($matrix as $row) {
    foreach ($row as $value) {
        if ($value % 2 === 0) {
            continue 2; // Skip to the NEXT ROW
        }
        echo "{$value} "; // Only prints odd numbers
    }
    echo "\n";
}
// 1 -> next row (2 is even)
// -> next row (4 is even)
// 7 9 -> (8 is even)
```

### `break` and `continue` in `switch`

In `switch`, `break` exits the switch; `continue` acts like `break`:

```php
<?php

// Inside a switch nested in a loop:
$values = [1, 2, 3, 'end']; // keeping 'end' for clarity (used as sentinel)

foreach ($values as $value) {
    if ($value === 'end') {
        break; // Exits foreach
    }

    switch ($value) {
        case 1:
            echo "one\n";
            break; // Exits switch
        case 2:
            echo "two\n";
            continue 2; // Exits switch AND continues foreach (next iteration)
        case 3:
            echo "three — this line never runs\n";
            break;
    }

    echo "After switch (value {$value})\n";
}
/*
Output:
one
After switch (value 1)
two
three — this line never runs
*/
```

---

## `goto`

PHP suporta `goto`. Raramente necessário. Use com moderação:

```php
<?php

$i = 0;

loop_start:
    $i++;
    echo "{$i} ";

    if ($i < 5) {
        goto loop_start;
    }
// 1 2 3 4 5
```

### Rare use case: breaking out of deeply nested loops

```php
<?php

// goto can replace break N in very complex cases
foreach ($data as $group) {
    foreach ($group as $item) {
        foreach ($item as $subItem) {
            if (exitCondition($subItem)) {
                goto clean_exit;
            }
            processItem($subItem);
        }
    }
}

clean_exit:
    echo "Processing complete or interrupted.\n";
```

> **Cuidado**: `goto` NÃO pode entrar em funções, classes, loops ou estruturas de controle. Só salta dentro do mesmo escopo.

```php
<?php
// This does NOT work:
goto inside;
for ($i = 0; $i < 5; $i++) {
    inside:
    echo "{$i}\n";
}
// Fatal error: 'goto' into loop
```

---

## `return`

Inside a function, `return` stops execution and returns a value. Outside a function, it returns control of the script (useful in conditional includes).

```php
<?php

function divide(float $a, float $b): float
{
    if ($b === 0.0) {
        throw new \DivisionByZeroError('Division by zero');
    }
    return $a / $b;
    // Code after return never runs
    echo "This line is never printed";
}

$result = divide(10, 2);
echo $result; // 5
```

### `return` in included files

```php
<?php
// config.php
return [
    'host'     => 'localhost',
    'database' => 'dev_db',
    'username' => 'root',
    'password' => 'password123',
];
```

```php
<?php
// index.php
$config = require 'config.php';
echo $config['host']; // localhost
```

---

## `declare`, `ticks`

### `declare(strict_types=1)`

Deve ser a primeira instrução do arquivo:

```php
<?php

declare(strict_types=1);

function add(int $a, int $b): int
{
    return $a + $b;
}

// add(1, "2"); // TypeError with strict_types
```

### `declare(ticks=N)`

Define um "tick" — um evento que dispara a cada `N` statements executados:

```php
<?php

declare(ticks=1);

function tickHandler(): void
{
    echo "tick!\n";
}

register_tick_function('tickHandler');

$a = 1;
$b = 2;
$c = $a + $b;
// Prints "tick!" after each statement
```

`ticks` raramente são usados. Profilers e ferramentas de debug podem usá-los.

### `declare(encoding='UTF-8')`

Especifica a codificação do script (PHP 5.6+):

```php
<?php

declare(encoding='UTF-8');

// The script is interpreted as UTF-8
```

---

## Alternative syntax for templates

PHP oferece sintaxe alternativa para estruturas de controle, ideal pra templates com HTML:

### `if:` / `endif;`

```php
<?php if ($isLoggedIn): ?>
    <div class="dashboard">
        <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
        <a href="/profile">My Profile</a>
    </div>
<?php else: ?>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="/login">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Login</button>
        </form>
    </div>
<?php endif; ?>
```

### `foreach:` / `endforeach;`

```php
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $i => $product): ?>
        <tr class="<?= $product['stock'] === 0 ? 'out-of-stock' : '' ?>">
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td>R$ <?= number_format($product['price'], 2, ',', '.') ?></td>
            <td><?= $product['stock'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
```

### `for:` / `endfor;`

```php
<select name="year">
    <option value="">Select year</option>
    <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
        <option value="<?= $year ?>"><?= $year ?></option>
    <?php endfor; ?>
</select>
```

### `while:` / `endwhile;`

```php
<h2>Recent posts</h2>
<?php while ($post = $posts->fetch()): ?>
    <article>
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <p><?= nl2br(htmlspecialchars($post['summary'])) ?></p>
        <time><?= date('m/d/Y', strtotime($post['published_at'])) ?></time>
    </article>
<?php endwhile; ?>
```

### `switch:` / `endswitch;`

```php
<?php switch ($userType): ?>
    <?php case 'admin': ?>
        <nav class="admin-menu">
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/users">Users</a>
            <a href="/admin/settings">Settings</a>
        </nav>
        <?php break; ?>

    <?php case 'editor': ?>
        <nav class="editor-menu">
            <a href="/editor/posts">Posts</a>
            <a href="/editor/media">Media</a>
        </nav>
        <?php break; ?>

    <?php default: ?>
        <nav class="user-menu">
            <a href="/profile">Profile</a>
            <a href="/my-posts">My Posts</a>
        </nav>
<?php endswitch; ?>
```

---

## Putting it all together

A script combining multiple control structures:

```php
<?php

declare(strict_types=1);

function classifyNumbers(array $numbers): array
{
    $classification = [
        'even'      => [],
        'odd'       => [],
        'primes'    => [],
        'negatives' => [],
        'zeros'     => 0,
    ];

    foreach ($numbers as $number) {
        if ($number === 0) {
            $classification['zeros']++;
            continue;
        }

        if ($number < 0) {
            $classification['negatives'][] = $number;
            $number = abs($number);
        }

        if ($number % 2 === 0) {
            $classification['even'][] = $number;
        } else {
            $classification['odd'][] = $number;
        }

        if (isPrime($number)) {
            $classification['primes'][] = $number;
        }
    }

    return $classification;
}

function isPrime(int $n): bool
{
    if ($n <= 1) return false;
    if ($n <= 3) return true;

    for ($i = 2; $i <= sqrt($n); $i++) {
        if ($n % $i === 0) {
            return false;
        }
    }

    return true;
}

// Run it
$numbers = [-7, -3, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 17, 0, 42];
$result = classifyNumbers($numbers);

foreach ($result as $category => $values) {
    if ($category === 'zeros') {
        echo "Zeros found: {$values}\n";
    } else {
        echo ucfirst($category) . ": " . implode(', ', $values) . "\n";
    }
}
/*
Even: 2, 4, 6, 8, 10, 42
Odd: 7, 3, 3, 5, 7, 9, 11, 13, 17
Primes: 2, 7, 3, 3, 5, 7, 11, 13, 17
Negatives: -7, -3
Zeros found: 2
*/
```

---

## Referências

- **if/else/elseif**: [php.net/manual/pt_BR/control-structures.if.php](https://www.php.net/manual/pt_BR/control-structures.if.php)
- **switch**: [php.net/manual/pt_BR/control-structures.switch.php](https://www.php.net/manual/pt_BR/control-structures.switch.php)
- **match**: [php.net/manual/pt_BR/control-structures.match.php](https://www.php.net/manual/pt_BR/control-structures.match.php)
- **while/do-while**: [php.net/manual/pt_BR/control-structures.while.php](https://www.php.net/manual/pt_BR/control-structures.while.php) e [do-while](https://www.php.net/manual/pt_BR/control-structures.do.while.php)
- **for**: [php.net/manual/pt_BR/control-structures.for.php](https://www.php.net/manual/pt_BR/control-structures.for.php)
- **foreach**: [php.net/manual/pt_BR/control-structures.foreach.php](https://www.php.net/manual/pt_BR/control-structures.foreach.php)
- **break/continue**: [php.net/manual/pt_BR/control-structures.break.php](https://www.php.net/manual/pt_BR/control-structures.break.php) e [continue](https://www.php.net/manual/pt_BR/control-structures.continue.php)
- **goto**: [php.net/manual/pt_BR/control-structures.goto.php](https://www.php.net/manual/pt_BR/control-structures.goto.php)
- **return**: [php.net/manual/pt_BR/function.return.php](https://www.php.net/manual/pt_BR/function.return.php)
- **declare**: [php.net/manual/pt_BR/control-structures.declare.php](https://www.php.net/manual/pt_BR/control-structures.declare.php)
- **Sintaxe alternativa**: [php.net/manual/pt_BR/control-structures.alternative-syntax.php](https://www.php.net/manual/pt_BR/control-structures.alternative-syntax.php)
- **O que há de novo no PHP 8.0**: [php.net/manual/pt_BR/migration80.new-features.php](https://www.php.net/manual/pt_BR/migration80.new-features.php)

---

## Próximo módulo

[→ 06 — Functions](./06-funcoes.md)
