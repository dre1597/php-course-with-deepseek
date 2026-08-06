# 07 — Arrays em PHP

## Índice

1. [Arrays Indexados e Associativos](#arrays-indexados-e-associativos)
2. [Criação: array() vs Short Syntax](#criação-array-vs-short-syntax)
3. [Acesso e Modificação](#acesso-e-modificação)
4. [Funções de Pilha e Fila](#funções-de-pilha-e-fila)
5. [Manipulação: merge, combine, slice, splice](#manipulação-merge-combine-slice-splice)
6. [Busca em Arrays](#busca-em-arrays)
7. [Programação Funcional com Arrays](#programação-funcional-com-arrays)
8. [Novas Funções de Busca (PHP 8.4+)](#novas-funções-de-busca-php-84)
9. [Funções de Acesso Avançado](#funções-de-acesso-avançado)
10. [Ordenação de Arrays](#ordenação-de-arrays)
11. [Array Destructuring](#array-destructuring)
12. [Spread Operator em Arrays](#spread-operator-em-arrays)
13. [Arrays Multidimensionais](#arrays-multidimensionais)
14. [Iteração com foreach](#iteração-com-foreach)
15. [Referências](#referências)

---

## Arrays Indexados e Associativos

Arrays armazenam múltiplos valores sob um único nome. No PHP, arrays funcionam como lista ordenada (vetor), mapa associativo (dicionário/hashmap) ou combinação de ambos.

### Arrays Indexados

Usam índices numéricos sequenciais, começando de `0`:

```php
<?php

$fruits = ['Apple', 'Banana', 'Orange', 'Grape'];

echo $fruits[0];   // Apple
echo $fruits[1];   // Banana
echo $fruits[2];   // Orange
```

### Arrays Associativos

Usam chaves personalizadas (strings) para mapear valores:

```php
<?php

$user = [
    'name'   => 'Mary Smith',
    'email'  => 'mary@email.com',
    'age'    => 28,
    'active' => true,
];

echo $user['name'];   // Mary Smith
echo $user['email'];  // mary@email.com
```

### Arrays Mistos

É possível ter chaves numéricas e string no mesmo array:

```php
<?php

$data = [
    'id'      => 42,
    'name'    => 'Product X',
    0         => 'first',
    1         => 'second',
    'price'   => 99.90,
];

echo $data[0];        // first
echo $data['price'];  // 99.9
```

**Dica:** Em PHP, `$array['1']` e `$array[1]` referenciam a **mesma chave** — o PHP converte chaves numéricas em string para inteiro.

---

## Criação: array() vs Short Syntax

Prefira **sempre** a short syntax `[]`, introduzida no PHP 5.4. Ambas são idênticas:

```php
<?php

// Sintaxe antiga (evitar):
$list = array(1, 2, 3);

// Sintaxe moderna (preferida):
$list = [1, 2, 3];
```

### Chaves Automáticas

Se omitir a chave, o PHP usa o maior índice inteiro + 1:

```php
<?php

$items = ['a', 'b', 7 => 'c', 'd', 'e'];

print_r($items);
/*
Array
(
    [0] => a
    [1] => b
    [7] => c
    [8] => d
    [9] => e
)
*/
```

---

## Acesso e Modificação

### Leitura

```php
<?php

$config = [
    'debug' => true,
    'host'  => 'localhost',
    'port'  => 3306,
];

echo $config['host'];                // localhost

// Acesso seguro com null coalescing (PHP 7+)
echo $config['timeout'] ?? 30;       // 30 (chave não existe, usa default)

// null coalescing em cadeia
echo $config['database']['mysql']['port'] ?? 5432; // 5432
```

### Escrita e Atualização

```php
<?php

$person = ['name' => 'John'];

$person['age']  = 25;         // add new key
$person['name'] = 'John S.';  // update existing value
$person[]       = 'extra';    // append with auto index (0)

print_r($person);
/*
Array
(
    [name] => John S.
    [age] => 25
    [0] => extra
)
*/
```

### `unset()` — Remover Elementos

```php
<?php

$colors = ['red', 'green', 'blue', 'yellow'];
unset($colors[1]);                           // remove 'green'

print_r($colors);
/*
Array
(
    [0] => red
    [2] => blue    <- indices are NOT reindexed!
    [3] => yellow
)
*/
```

Para reindexar após `unset`, use `array_values()`:

```php
<?php

$colors = array_values($colors);
print_r($colors);
/*
Array
(
    [0] => red
    [1] => blue
    [2] => yellow
)
*/
```

---

## Funções de Pilha e Fila

PHP fornece funções para tratar arrays como **pilhas** (LIFO) e **filas** (FIFO):

### `array_push()` — Adicionar ao Final

```php
<?php

$stack = ['A', 'B'];
array_push($stack, 'C', 'D', 'E');

print_r($stack); // ['A', 'B', 'C', 'D', 'E']

// Equivalente mais performático:
$stack[] = 'F';
```

**Dica:** `$arr[] = $valor` é mais rápido que `array_push()` para um único elemento — evita overhead de chamada de função.

### `array_pop()` — Remover do Final

```php
<?php

$stack = ['A', 'B', 'C'];
$last = array_pop($stack);  // remove e retorna 'C'

echo $last;                 // C
print_r($stack);              // ['A', 'B']
```

### `array_shift()` — Remover do Início

```php
<?php

$queue = ['first', 'second', 'third'];
$served = array_shift($queue);  // remove and return 'first'

echo $served;                   // first
print_r($queue);                   // ['second', 'third']
```

**Cuidado:** `array_shift()` reindexa todas as chaves numéricas — O(n), custoso para arrays grandes.

### `array_unshift()` — Adicionar ao Início

```php
<?php

$queue = ['B', 'C'];
array_unshift($queue, 'A');

print_r($queue); // ['A', 'B', 'C']
```

---

## Manipulação: merge, combine, slice, splice

### `array_merge()` — Mesclar Arrays

Combina dois ou mais arrays. Chaves string são sobrescritas; chaves numéricas são reindexadas:

```php
<?php

$defaults = ['host' => 'localhost', 'port' => 3306, 'timeout' => 30];
$userConfig = ['host' => '192.168.1.10', 'user' => 'admin', 'timeout' => 60];

$final = array_merge($defaults, $userConfig);
print_r($final);
/*
Array
(
    [host] => 192.168.1.10    <- sobrescrito
    [port] => 3306
    [timeout] => 60           <- sobrescrito
    [user] => admin
)
*/
```

```php
<?php

// Merge de arrays indexados
$array1 = [10, 20, 30];
$array2 = [40, 50];
$merged = array_merge($array1, $array2);

print_r($merged); // [10, 20, 30, 40, 50]
```

### `array_combine()` — Combinar Chaves e Valores

Cria um array associativo usando um array para chaves e outro para valores:

```php
<?php

$keys = ['name', 'email', 'age'];
$values = ['Charles', 'charles@email.com', 32];

$user = array_combine($keys, $values);
print_r($user);
/*
Array
(
    [name] => Charles
    [email] => charles@email.com
    [age] => 32
)
*/
```

**Cuidado:** Arrays com tamanhos diferentes lançam `ValueError` (PHP 8.0+).

### `array_slice()` — Extrair Fatia

Extrai uma porção do array **sem modificar o original**:

```php
<?php

$numbers = [10, 20, 30, 40, 50, 60];

$fatia = array_slice($numbers, 2, 3);     // a partir do índice 2, pega 3 elementos
print_r($fatia);                           // [30, 40, 50]

print_r(array_slice($numbers, -2));        // últimos 2: [50, 60]
print_r(array_slice($numbers, 0, 4));      // primeiros 4: [10, 20, 30, 40]
```

O quarto parâmetro (`preserve_keys`) mantém as chaves originais:

```php
<?php

$date = [5 => 'a', 10 => 'b', 15 => 'c'];
print_r(array_slice($date, 1, 2, true));
/*
Array
(
    [10] => b
    [15] => c
)
*/
```

### `array_splice()` — Remover/Substituir Fatia

Remove ou substitui elementos **modificando o array original**:

```php
<?php

$colors = ['red', 'green', 'blue', 'yellow', 'purple'];

// Remove 2 elements starting at index 2
$removed = array_splice($colors, 2, 2);
print_r($removed); // ['blue', 'yellow']
print_r($colors);     // ['red', 'green', 'purple']
```

Inserir elementos:

```php
<?php

$fruits = ['apple', 'banana', 'orange'];
array_splice($fruits, 1, 0, ['grape', 'pear']);  // insert at position 1, remove 0

print_r($fruits); // ['apple', 'grape', 'pear', 'banana', 'orange']
```

---

## Busca em Arrays

### `in_array()` — Verificar se Valor Existe

```php
<?php

$fruits = ['apple', 'banana', 'orange'];

var_dump(in_array('banana', $fruits));   // bool(true)
var_dump(in_array('grape', $fruits));    // bool(false)

// Com verificação estrita de tipo (terceiro parâmetro):
$numbers = [1, 2, '3'];
var_dump(in_array(3, $numbers));         // bool(true)  — coerção!
var_dump(in_array(3, $numbers, true));   // bool(false) — estrito
```

### `array_key_exists()` — Verificar se Chave Existe

```php
<?php

$user = ['name' => 'Anna', 'email' => 'anna@email.com'];

var_dump(array_key_exists('name', $user));    // bool(true)
var_dump(array_key_exists('age', $user));   // bool(false)

// Diferente de isset():
$data = ['value' => null];
var_dump(array_key_exists('value', $data)); // bool(true)
var_dump(isset($data['value']));            // bool(false) — NULL é considerado "não setado"
```

### `array_search()` — Encontrar Chave por Valor

```php
<?php

$colors = [
    'primary'   => 'red',
    'secondary' => 'blue',
    'tertiary'  => 'green',
];

$key = array_search('blue', $colors);
echo $key; // secondary

// If not found:
$notFound = array_search('purple', $colors);
var_dump($notFound); // bool(false)
```

**Cuidado:** `array_search()` pode retornar `0` (índice zero), que é falsy. Compare com `=== false`:

```php
<?php

$values = [0 => 'first', 1 => 'second'];
$result = array_search('first', $values);

if ($result === false) {
    echo 'Not found';
} else {
    echo "Found at index: {$result}"; // Found at index: 0
}
```

---

## Programação Funcional com Arrays

### `array_map()` — Transformar Cada Elemento

Aplica uma função callback a cada elemento e retorna um novo array:

```php
<?php

$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn(int $n): int => $n * 2, $numbers);
print_r($doubled); // [2, 4, 6, 8, 10]

// Com múltiplos arrays (PHP processa em paralelo):
$array1 = [10, 20, 30];
$array2 = [1, 2, 3];
$sums = array_map(fn(int $first, int $second): int => $first + $second, $array1, $array2);
print_r($sums); // [11, 22, 33]

// With first-class callable (PHP 8.1+):
$names = [' ana ', ' Charles ', ' BEA '];
$clean = array_map(trim(...), $names);
print_r($clean); // ['ana', 'Charles', 'BEA']
```

**Dica:** Com callback `null`, `array_map()` junta os arrays em um array multidimensional.

### `array_filter()` — Filtrar Elementos

Filtra elementos com base em um callback que retorna `true`:

```php
<?php

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$evens = array_filter($numbers, fn(int $number): bool => $number % 2 === 0);
print_r($evens); // [1 => 2, 3 => 4, 5 => 6, 7 => 8, 9 => 10]

// Keep only non-empty strings:
$data = ['PHP', '', '8.5', false, null, '2026'];
$valid = array_filter($data);
print_r($data); // ['PHP', '8.5', '2026']
```

Sem callback, `array_filter()` remove valores falsy (`false`, `null`, `0`, `''`, `[]`).

### `array_reduce()` — Reduzir a um Único Valor

Acumula valores:

```php
<?php

$numbers = [1, 2, 3, 4, 5];

$sum = array_reduce($numbers, fn(int $accumulator, int $current): int => $accumulator + $current, 0);
echo $sum; // 15

// Calcular o produto de todos os elementos:
$product = array_reduce($numbers, fn(int $acc, int $number): int => $acc * $number, 1);
echo $product; // 120 (5!)

// Build a complex structure (grouping):
$orders = [
    ['client' => 'Anna',  'total' => 150.00],
    ['client' => 'John',  'total' => 200.00],
    ['client' => 'Anna',  'total' => 75.00],
    ['client' => 'John',  'total' => 50.00],
];

$byClient = array_reduce(
    $orders,
    function (array $acc, array $order): array {
        $client = $order['client'];
        $acc[$client] = ($acc[$client] ?? 0) + $order['total'];
        return $acc;
    },
    [],
);

print_r($byClient);
/*
Array
(
    [Anna] => 225
    [John] => 250
)
*/
```

### `array_walk()` — Percorrer e Modificar (in-place)

Aplica um callback a cada elemento, podendo modificar o array original (se passado por referência):

```php
<?php

$values = [10.5, 20.3, 30.7, 40.1];

array_walk($values, function (float &$value, int $index): void {
    $value = round($value);
});

print_r($values); // [11, 20, 31, 40]
```

Com callback que recebe argumento adicional:

```php
<?php

$prices = [100, 200, 300];
$tax = 0.1; // 10%

array_walk($prices, function (float &$price, int $index, float $rate): void {
    $price += $price * $rate;
}, $tax);

print_r($prices); // [110, 220, 330]
```

---

## Novas Funções de Busca (PHP 8.4+)

### `array_find()` — PHP 8.4+

Retorna o **primeiro elemento** que satisfaz o callback:

```php
<?php

$users = [
    ['name' => 'Anna',   'active' => true,  'points' => 120],
    ['name' => 'John',   'active' => false, 'points' => 200],
    ['name' => 'Mary',   'active' => true,  'points' => 85],
    ['name' => 'Peter',  'active' => true,  'points' => 150],
];

$firstInactive = array_find($users, fn(array $user): bool => !$user['active']);
print_r($firstInactive);
/*
Array
(
    [name] => John
    [active] => false
    [points] => 200
)
*/

// Se nenhum elemento for encontrado, retorna null
$notFound = array_find($users, fn(array $user): bool => $user['points'] > 999);
var_dump($notFound); // NULL
```

### `array_find_key()` — PHP 8.4+

Retorna a **chave** do primeiro elemento que satisfaz o callback:

```php
<?php

$stock = [
    'P001' => ['name' => 'Notebook',   'qty' => 0],
    'P002' => ['name' => 'Mouse',      'qty' => 45],
    'P003' => ['name' => 'Keyboard',   'qty' => 0],
    'P004' => ['name' => 'Monitor',    'qty' => 12],
];

$firstZero = array_find_key($stock, fn(array $product): bool => $product['qty'] === 0);
echo $firstZero; // P001

// Not found returns null
$key = array_find_key($stock, fn(array $product): bool => $product['qty'] > 100);
var_dump($key); // NULL
```

### `array_any()` — PHP 8.4+

Verifica se **pelo menos um** elemento satisfaz o callback (semelhante a `some()` do JavaScript):

```php
<?php

$values = [2, 4, 6, 7, 8, 10];

$hasOdd = array_any($values, fn(int $number): bool => $number % 2 !== 0);
var_dump($hasOdd); // bool(true)

$allNegative = array_any($values, fn(int $number): bool => $number < 0);
var_dump($allNegative); // bool(false)

// Exemplo prático: check se há usuários premium
$users = [
    ['plan' => 'basic'],
    ['plan' => 'basic'],
    ['plan' => 'premium'],
];

$hasPremium = array_any($users, fn(array $user): bool => $user['plan'] === 'premium');
var_dump($hasPremium); // bool(true)
```

### `array_all()` — PHP 8.4+

Verifica se **todos** os elementos satisfazem o callback (semelhante a `every()` do JavaScript):

```php
<?php

$ages = [18, 25, 30, 22, 19];

$allAdults = array_all($ages, fn(int $age): bool => $age >= 18);
var_dump($allAdults); // bool(true)

$allEven = array_all($ages, fn(int $age): bool => $age % 2 === 0);
var_dump($allEven); // bool(false)

// Exemplo prático: todas as senhas atendem ao critério mínimo
$passwords = ['ABcd1234!', 'XYzw5678@', 'PQrs9012#'];
$allSecure = array_all(
    $passwords,
    fn(string $string): bool => strlen($string) >= 8 && preg_match('/[A-Z]/', $string) && preg_match('/\d/', $string),
);
var_dump($allSecure); // bool(true)
```

**Dica:** `array_any()` e `array_all()` usam **short-circuit** — param no primeiro elemento que define o resultado.

---

## Funções de Acesso Avançado

### `array_key_first()` e `array_key_last()` (PHP 7.3+)

```php
<?php

$data = ['a' => 1, 'b' => 2, 'c' => 3];

echo array_key_first($data); // a
echo array_key_last($data);  // c

// Equivalente sem a função (menos eficiente):
// array_key_first: array_keys($data)[0] ?? null
```

### `array_first()` e `array_last()` — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Estas funções retornam o **primeiro** ou **último valor** do array sem a necessidade de fazer `reset()` / `end()` ou `$arr[array_key_first($arr)]`:

```php
<?php

$numbers = ['a' => 10, 'b' => 20, 'c' => 30, 'd' => 40];

$first = array_first($numbers);
$last   = array_last($numbers);

echo $first; // 10
echo $last;   // 40

// Com array vazio, retornam null:
$empty = [];
var_dump(array_first($empty)); // NULL
var_dump(array_last($empty));  // NULL
```

Comparação com abordagens anteriores:

```php
<?php

$arr = ['x' => 100, 'y' => 200, 'z' => 300];

// PHP < 8.5: várias opções, todas verbosas
$first = reset($arr);                                    // modifica o ponteiro interno
$first = $arr[array_key_first($arr)] ?? null;            // verboso
$first = array_values($arr)[0] ?? null;                  // ineficiente

// PHP 8.5+: limpo e direto
$first = array_first($arr);                              // 100, não modifica o ponteiro
$last  = array_last($arr);                               // 300, não modifica o ponteiro
```

### `array_values()` e `array_keys()`

```php
<?php

$user = ['name' => 'Anna', 'email' => 'anna@email.com', 'age' => 28];

$keys = array_keys($user);
print_r($keys); // ['name', 'email', 'age']

$values = array_values($user);
print_r($values); // ['Anna', 'anna@email.com', 28]

// array_keys with value filter:
$numbers = [10, 20, 10, 30, 10, 40];
$indicesOf10 = array_keys($numbers, 10);
print_r($indicesOf10); // [0, 2, 4]
```

---

## Ordenação de Arrays

### Ordenação por Valor

| Função | Descrição | Mantém Chaves? |
|--------|-----------|----------------|
| `sort($arr)` | Ordem crescente | Não (reindexa) |
| `rsort($arr)` | Ordem decrescente | Não (reindexa) |
| `asort($arr)` | Ordem crescente | Sim |
| `arsort($arr)` | Ordem decrescente | Sim |

```php
<?php

$numbers = [30, 10, 50, 20, 40];

sort($numbers);
print_r($numbers); // [10, 20, 30, 40, 50]

rsort($numbers);
print_r($numbers); // [50, 40, 30, 20, 10]
```

```php
<?php

$scores = [
    'John'  => 85,
    'Mary'  => 92,
    'Peter' => 78,
    'Anna'  => 95,
];

asort($scores);  // sort by value, preserve keys
print_r($scores);
/*
Array
(
    [Peter] => 78
    [John] => 85
    [Mary] => 92
    [Anna] => 95
)
*/

arsort($scores); // descending, preserve keys
print_r($scores);
/*
Array
(
    [Anna] => 95
    [Mary] => 92
    [John] => 85
    [Peter] => 78
)
*/
```

### Ordenação por Chave

| Função | Descrição |
|--------|-----------|
| `ksort($arr)` | Ordem crescente pelas chaves |
| `krsort($arr)` | Ordem decrescente pelas chaves |

```php
<?php

$date = [
    'zebra' => 1,
    'alpha' => 2,
    'gama'  => 3,
    'beta'  => 4,
];

ksort($date);
print_r($date);
/*
Array
(
    [alpha] => 2
    [beta] => 4
    [gama] => 3
    [zebra] => 1
)
*/
```

### Ordenação Personalizada com `usort()`

`usort()` aceita um callback de comparação (deve retornar `-1`, `0` ou `1`):

```php
<?php

$products = [
    ['name' => 'Notebook',  'price' => 3500.00],
    ['name' => 'Mouse',     'price' => 89.90],
    ['name' => 'Keyboard',  'price' => 199.90],
    ['name' => 'Monitor',   'price' => 1200.00],
];

// Sort by ascending price
usort($products, fn(array $firstProduct, array $secondProduct): int => $firstProduct['price'] <=> $secondProduct['price']);

print_r($products);
/*
Array
(
    [0] => [Mouse, 89.9]
    [1] => [Keyboard, 199.9]
    [2] => [Monitor, 1200]
    [3] => [Notebook, 3500]
)
*/
```

**Dica:** Use o operador spaceship `<=>` para comparações. Ele retorna `-1`, `0` ou `1`.

Exemplo de ordenação por múltiplos critérios:

```php
<?php

$people = [
    ['name' => 'Anna',  'age' => 30],
    ['name' => 'John',  'age' => 25],
    ['name' => 'Mary',  'age' => 30],
    ['name' => 'Peter', 'age' => 25],
];

usort($people, function (array $first, array $second): int {
    // First by age, then by name
    return $first['age'] <=> $second['age']
        ?: $first['name'] <=> $second['name'];
});

print_r($people);
/*
[0] => [John, 25]
[1] => [Peter, 25]
[2] => [Anna, 30]
[3] => [Mary, 30]
*/
```

---

## Array Destructuring

**PHP 7.1+** — Permite extrair valores de um array direto para variáveis:

```php
<?php

$coordinates = [10, 20, 30];

[$coordX, $coordY, $coordZ] = $coordinates;
echo "x={$coordX}, y={$coordY}, z={$coordZ}"; // x=10, y=20, z=30

// Pular elementos:
[,, $third] = $coordinates;
echo $third; // 30
```

### Destructuring Associativo (PHP 7.1+)

```php
<?php

$user = [
    'name'    => 'Charles',
    'email'   => 'charles@email.com',
    'city'    => 'São Paulo',
];

['name' => $name, 'email' => $email] = $user;
echo $name;  // Charles
echo $email; // charles@email.com
```

### Com foreach

```php
<?php

$students = [
    ['id' => 1, 'name' => 'Anna',  'grade' => 9.5],
    ['id' => 2, 'name' => 'John',  'grade' => 7.0],
    ['id' => 3, 'name' => 'Bea',   'grade' => 8.5],
];

foreach ($students as ['name' => $name, 'grade' => $grade]) {
    echo "{$name}: {$grade}" . PHP_EOL;
}
// Anna: 9.5
// John: 7.0
// Bea: 8.5
```

---

## Spread Operator em Arrays (PHP 7.4+)

O operador spread `...` desempacota arrays dentro de novas declarações:

```php
<?php

$admin = ['name' => 'Admin', 'role' => 'admin'];
$data = ['email' => 'admin@site.com', 'active' => true];

$user = [...$admin, ...$data];
print_r($user);
/*
Array
(
    [name] => Admin
    [role] => admin
    [email] => admin@site.com
    [active] => 1
)
*/
```

### Combinando Arrays Indexados

```php
<?php

$classA = ['Anna', 'John', 'Mary'];
$classB = ['Peter', 'Bea'];
$classC = ['Charles'];

$all = [...$classA, ...$classB, ...$classC];
print_r($all); // ['Anna', 'John', 'Mary', 'Peter', 'Bea', 'Charles']
```

### Adicionando Elementos no Meio

```php
<?php

$original = ['first', 'fourth'];
$new = [$original[0], ...['second', 'third'], $original[1]];

print_r($new); // ['first', 'second', 'third', 'fourth']
```

**Cuidado:** Em arrays associativos, chaves duplicadas fazem a **última** prevalecer (como `array_merge`):

```php
<?php

$array1 = ['x' => 1, 'y' => 2];
$array2 = ['y' => 200, 'z' => 3];

$mergedResult = [...$array1, ...$array2];
print_r($mergedResult); // ['x' => 1, 'y' => 200, 'z' => 3]
```

---

## Arrays Multidimensionais

Arrays podem conter outros arrays como valores, criando **matrizes** ou estruturas hierárquicas:

```php
<?php

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

echo $matrix[1][2]; // 6 (linha 1, coluna 2)

// Percorrendo com foreach aninhado:
foreach ($matrix as $row => $values) {
    foreach ($values as $column => $value) {
        echo "[{$row}][{$column}] = {$value}" . PHP_EOL;
    }
}
```

### Estruturas de Dados Complexas

```php
<?php

$ecommerce = [
    'categories' => [
        [
            'id'     => 1,
            'name'   => 'Electronics',
            'products' => [
                ['id' => 101, 'name' => 'Smartphone',   'price' => 2500.00, 'stock' => 50],
                ['id' => 102, 'name' => 'Notebook',     'price' => 4500.00, 'stock' => 20],
            ],
        ],
        [
            'id'     => 2,
            'name'   => 'Books',
            'products' => [
                ['id' => 201, 'name' => 'Modern PHP',   'price' => 89.90,  'stock' => 100],
                ['id' => 202, 'name' => 'Clean Code',   'price' => 120.00, 'stock' => 75],
            ],
        ],
    ],
];

// Total stock value for Electronics
$totalElectronics = array_reduce(
    $ecommerce['categories'][0]['products'],
    fn(float $sum, array $product): float => $sum + ($product['price'] * $product['stock']),
    0.0,
);
echo "Total stock (Electronics): $ " . number_format($totalElectronics, 2, '.', ',');
// Total stock (Electronics): $ 215,000.00
```

### Flatten Multidimensional Array

```php
<?php

$nested = [[1, 2], [3, 4], [5, 6]];

$flat = array_merge(...$nested);
print_r($flat); // [1, 2, 3, 4, 5, 6]

// Functional alternative with array_reduce:
$flat2 = array_reduce($nested, fn(array $acc, array $sub): array => [...$acc, ...$sub], []);
print_r($flat2); // [1, 2, 3, 4, 5, 6]
```

---

## Iteração com `foreach`

### Sintaxe Básica

```php
<?php

// Values only
$colors = ['red', 'green', 'blue'];
foreach ($colors as $color) {
    echo $color . PHP_EOL;
}

// Key and value
$user = ['name' => 'Mary', 'email' => 'mary@email.com', 'age' => 28];
foreach ($user as $key => $value) {
    echo "{$key}: {$value}" . PHP_EOL;
}
/*
name: Mary
email: mary@email.com
age: 28
*/
```

### Modificação por Referência no `foreach`

```php
<?php

$numbers = [1, 2, 3, 4, 5];

foreach ($numbers as &$value) {
    $value *= 2;
}
unset($value); // IMPORTANT: release the reference

print_r($numbers); // [2, 4, 6, 8, 10]
```

**Cuidado:** Sempre faça `unset($value)` após modificar por referência em `foreach`. Senão a variável fica como referência ao último elemento, causando bugs sutis:

```php
<?php

$items = [1, 2, 3];
foreach ($items as &$value) { $value *= 10; }
// unset($value); // forgotten!

foreach ($items as $value) {
    // $value is still a reference to $items[2]!
    // This corrupts the original array.
}
print_r($items); // unexpected result without unset
```

### Uso de `else` com `foreach`

```php
<?php

$empty = [];

foreach ($empty as $item) {
    echo $item;
} else {
    echo 'Array is empty!';
}
```

### Iteração com Objetos `ArrayIterator`

```php
<?php

$date = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);

foreach ($date as $key => $value) {
    echo "{$key} => {$value}" . PHP_EOL;
}
```

---

## Referências

- [Documentação oficial: Arrays](https://www.php.net/manual/en/language.types.array.php)
- [Funções de Array](https://www.php.net/manual/en/ref.array.php)
- [array_map()](https://www.php.net/manual/en/function.array-map.php)
- [array_filter()](https://www.php.net/manual/en/function.array-filter.php)
- [array_reduce()](https://www.php.net/manual/en/function.array-reduce.php)
- [array_find() — PHP 8.4](https://www.php.net/manual/en/function.array-find.php)
- [array_find_key() — PHP 8.4](https://www.php.net/manual/en/function.array-find-key.php)
- [array_any() — PHP 8.4](https://www.php.net/manual/en/function.array-any.php)
- [array_all() — PHP 8.4](https://www.php.net/manual/en/function.array-all.php)
- [array_first() — PHP 8.5](https://www.php.net/manual/en/function.array-first.php)
- [array_last() — PHP 8.5](https://www.php.net/manual/en/function.array-last.php)
- [Spread Operator em Arrays](https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.spread-operator)
- [Array Destructuring](https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring)
- [Ordenação de Arrays](https://www.php.net/manual/en/array.sorting.php)
- [Estruturas de controle: foreach](https://www.php.net/manual/en/control-structures.foreach.php)

---

> **Capítulo anterior:** [06 — Funções](./06-funcoes.md)
> **Próximo capítulo:** [08 — Strings](./08-strings.md)
