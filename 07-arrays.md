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

Em PHP, arrays são estruturas de dados que armazenam múltiplos valores sob um único nome. Diferente de muitas linguagens, o array do PHP é uma **estrutura híbrida** — funciona como lista ordenada (vetor), mapa associativo (dicionário/hashmap) ou combinação de ambos.

### Arrays Indexados

Usam índices numéricos sequenciais, começando de `0`:

```php
<?php

$fruits = ['Maçã', 'Banana', 'Laranja', 'Uva'];

echo $fruits[0];   // Maçã
echo $fruits[1];   // Banana
echo $fruits[2];   // Laranja
```

### Arrays Associativos

Usam chaves personalizadas (strings) para mapear valores:

```php
<?php

$user = [
    'name'   => 'Maria Silva',
    'email'  => 'maria@email.com',
    'age'    => 28,
    'active' => true,
];

echo $user['name'];   // Maria Silva
echo $user['email'];  // maria@email.com
```

### Arrays Mistos

É possível ter chaves numéricas e string no mesmo array:

```php
<?php

$data = [
    'id'      => 42,
    'name'    => 'Product X',
    0         => 'primeiro',
    1         => 'segundo',
    'price'   => 99.90,
];

echo $data[0];        // primeiro
echo $data['price'];  // 99.9
```

💡 **Dica:** Em PHP, `$array['1']` e `$array[1]` referenciam **a mesma chave**, pois o PHP converte chaves numéricas em string para inteiro.

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

Se você não especificar uma chave, o PHP atribui a chave como o maior índice inteiro usado + 1:

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

$person = ['name' => 'João'];

$person['age']  = 25;         // adiciona nova chave
$person['name'] = 'João S.';  // atualiza valor existente
$person[]       = 'extra';     // adiciona com índice automático (0)

print_r($person);
/*
Array
(
    [name] => João S.
    [age] => 25
    [0] => extra
)
*/
```

### `unset()` — Remover Elementos

```php
<?php

$colors = ['vermelho', 'verde', 'azul', 'amarelo'];
unset($colors[1]);                         // remove 'verde'

print_r($colors);
/*
Array
(
    [0] => vermelho
    [2] => azul    <- índices NÃO são reindexados!
    [3] => amarelo
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
    [0] => vermelho
    [1] => azul
    [2] => amarelo
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

💡 **Dica:** Para adicionar um único elemento, `$arr[] = $valor` é mais rápido que `array_push()`, pois não envolve overhead de chamada de função.

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

$queue = ['primeiro', 'segundo', 'terceiro'];
$served = array_shift($queue);  // remove e retorna 'primeiro'

echo $served;                   // primeiro
print_r($queue);                   // ['segundo', 'terceiro']
```

⚠️ **Cuidado:** `array_shift()` reindexa todas as chaves numéricas, o que é custoso para arrays grandes (O(n)).

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
$values = ['Carlos', 'carlos@email.com', 32];

$user = array_combine($keys, $values);
print_r($user);
/*
Array
(
    [name] => Carlos
    [email] => carlos@email.com
    [age] => 32
)
*/
```

⚠️ **Cuidado:** Ambos os arrays devem ter o mesmo número de elementos, senão o PHP lança um `ValueError` (PHP 8.0+).

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

$colors = ['vermelho', 'verde', 'azul', 'amarelo', 'roxo'];

// Remove 2 elementos a partir do índice 2
$removidos = array_splice($colors, 2, 2);
print_r($removidos); // ['azul', 'amarelo']
print_r($colors);     // ['vermelho', 'verde', 'roxo']
```

Inserir elementos:

```php
<?php

$fruits = ['maçã', 'banana', 'laranja'];
array_splice($fruits, 1, 0, ['uva', 'pera']);  // insere na posição 1, remove 0

print_r($fruits); // ['maçã', 'uva', 'pera', 'banana', 'laranja']
```

---

## Busca em Arrays

### `in_array()` — Verificar se Valor Existe

```php
<?php

$fruits = ['maçã', 'banana', 'laranja'];

var_dump(in_array('banana', $fruits));   // bool(true)
var_dump(in_array('uva', $fruits));      // bool(false)

// Com verificação estrita de tipo (terceiro parâmetro):
$numbers = [1, 2, '3'];
var_dump(in_array(3, $numbers));         // bool(true)  — coerção!
var_dump(in_array(3, $numbers, true));   // bool(false) — estrito
```

### `array_key_exists()` — Verificar se Chave Existe

```php
<?php

$user = ['name' => 'Ana', 'email' => 'ana@email.com'];

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
    'primaria'   => 'vermelho',
    'secundaria' => 'azul',
    'terciaria'  => 'verde',
];

$key = array_search('azul', $colors);
echo $key; // secundaria

// Se não encontrar:
$notFound = array_search('roxo', $colors);
var_dump($notFound); // bool(false)
```

⚠️ **Cuidado:** `array_search()` retorna `false` se não encontrar, mas também pode retornar `0` (índice zero, que é falsy). Sempre compare com `=== false`:

```php
<?php

$values = [0 => 'primeiro', 1 => 'segundo'];
$result = array_search('primeiro', $values);

if ($result === false) {
    echo 'Não encontrado';
} else {
    echo "Encontrado no índice: {$result}"; // Encontrado no índice: 0
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

// Com first-class callable (PHP 8.1+):
$names = [' ana ', ' Carlos ', ' BIA '];
$clean = array_map(trim(...), $names);
print_r($clean); // ['ana', 'Carlos', 'BIA']
```

💡 **Dica:** Se o callback for `null`, `array_map()` junta os arrays passados em um array multidimensional (comportamento similar a `array_merge` nas posições).

### `array_filter()` — Filtrar Elementos

Filtra elementos com base em um callback que retorna `true`:

```php
<?php

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$evens = array_filter($numbers, fn(int $number): bool => $number % 2 === 0);
print_r($evens); // [1 => 2, 3 => 4, 5 => 6, 7 => 8, 9 => 10]

// Manter apenas strings não vazias:
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

// Construir uma estrutura complexa (ex: agrupamento):
$orders = [
    ['client' => 'Ana',  'total' => 150.00],
    ['client' => 'João', 'total' => 200.00],
    ['client' => 'Ana',  'total' => 75.00],
    ['client' => 'João', 'total' => 50.00],
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
    [Ana] => 225
    [João] => 250
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
    ['name' => 'Ana',   'active' => true,  'points' => 120],
    ['name' => 'João',  'active' => false, 'points' => 200],
    ['name' => 'Maria', 'active' => true,  'points' => 85],
    ['name' => 'Pedro', 'active' => true,  'points' => 150],
];

$firstInactive = array_find($users, fn(array $user): bool => !$user['active']);
print_r($firstInactive);
/*
Array
(
    [name] => João
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
    'P003' => ['name' => 'Teclado',    'qty' => 0],
    'P004' => ['name' => 'Monitor',    'qty' => 12],
];

$firstZero = array_find_key($stock, fn(array $product): bool => $product['qty'] === 0);
echo $firstZero; // P001

// Não encontrado retorna null
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

💡 **Dica:** `array_any()` e `array_all()` são **short-circuit** — param de percorrer o array assim que a condição é determinada.

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

$user = ['name' => 'Ana', 'email' => 'ana@email.com', 'age' => 28];

$keys = array_keys($user);
print_r($keys); // ['name', 'email', 'age']

$values = array_values($user);
print_r($values); // ['Ana', 'ana@email.com', 28]

// array_keys com filtro de valor:
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
    'João'  => 85,
    'Maria' => 92,
    'Pedro' => 78,
    'Ana'   => 95,
];

asort($scores);  // ordena por valor, mantém chaves
print_r($scores);
/*
Array
(
    [Pedro] => 78
    [João] => 85
    [Maria] => 92
    [Ana] => 95
)
*/

arsort($scores); // decrescente, mantém chaves
print_r($scores);
/*
Array
(
    [Ana] => 95
    [Maria] => 92
    [João] => 85
    [Pedro] => 78
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
    ['name' => 'Teclado',   'price' => 199.90],
    ['name' => 'Monitor',   'price' => 1200.00],
];

// Ordenar por preço crescente
usort($products, fn(array $firstProduct, array $secondProduct): int => $firstProduct['price'] <=> $secondProduct['price']);

print_r($products);
/*
Array
(
    [0] => [Mouse, 89.9]
    [1] => [Teclado, 199.9]
    [2] => [Monitor, 1200]
    [3] => [Notebook, 3500]
)
*/
```

💡 **Dica:** Use o operador spaceship `<=>` para comparações limpas. Ele retorna `-1`, `0` ou `1`.

Exemplo de ordenação por múltiplos critérios:

```php
<?php

$people = [
    ['name' => 'Ana',   'age' => 30],
    ['name' => 'João',  'age' => 25],
    ['name' => 'Maria', 'age' => 30],
    ['name' => 'Pedro', 'age' => 25],
];

usort($people, function (array $first, array $second): int {
    // Primeiro por idade, depois por nome
    return $first['age'] <=> $second['age']
        ?: $first['name'] <=> $second['name'];
});

print_r($people);
/*
[0] => [João, 25]
[1] => [Pedro, 25]
[2] => [Ana, 30]
[3] => [Maria, 30]
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
    'name'    => 'Carlos',
    'email'   => 'carlos@email.com',
    'city'  => 'São Paulo',
];

['name' => $name, 'email' => $email] = $user;
echo $name;  // Carlos
echo $email; // carlos@email.com
```

### Com foreach

```php
<?php

$students = [
    ['id' => 1, 'name' => 'Ana',  'grade' => 9.5],
    ['id' => 2, 'name' => 'João', 'grade' => 7.0],
    ['id' => 3, 'name' => 'Bia',  'grade' => 8.5],
];

foreach ($students as ['name' => $name, 'grade' => $grade]) {
    echo "{$name}: {$grade}" . PHP_EOL;
}
// Ana: 9.5
// João: 7.0
// Bia: 8.5
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

$classA = ['Ana', 'João', 'Maria'];
$classB = ['Pedro', 'Bia'];
$classC = ['Carlos'];

$all = [...$classA, ...$classB, ...$classC];
print_r($all); // ['Ana', 'João', 'Maria', 'Pedro', 'Bia', 'Carlos']
```

### Adicionando Elementos no Meio

```php
<?php

$original = ['primeiro', 'quarto'];
$new = [$original[0], ...['segundo', 'terceiro'], $original[1]];

print_r($new); // ['primeiro', 'segundo', 'terceiro', 'quarto']
```

⚠️ **Cuidado:** Para arrays associativos, se houver chaves duplicadas, a **última** prevalece (comportamento similar a `array_merge`):

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
            'name'   => 'Eletrônicos',
            'products' => [
                ['id' => 101, 'name' => 'Smartphone',   'price' => 2500.00, 'stock' => 50],
                ['id' => 102, 'name' => 'Notebook',     'price' => 4500.00, 'stock' => 20],
            ],
        ],
        [
            'id'     => 2,
            'name'   => 'Livros',
            'products' => [
                ['id' => 201, 'name' => 'PHP Moderno',  'price' => 89.90,  'stock' => 100],
                ['id' => 202, 'name' => 'Clean Code',   'price' => 120.00, 'stock' => 75],
            ],
        ],
    ],
];

// Valor total do estoque de Eletrônicos
$totalElectronics = array_reduce(
    $ecommerce['categories'][0]['products'],
    fn(float $sum, array $product): float => $sum + ($product['price'] * $product['stock']),
    0.0,
);
echo "Total em estoque (Eletrônicos): R$ " . number_format($totalElectronics, 2, ',', '.');
// Total em estoque (Eletrônicos): R$ 215.000,00
```

### Aplanar Array Multidimensional

```php
<?php

$nested = [[1, 2], [3, 4], [5, 6]];

$flat = array_merge(...$nested);
print_r($flat); // [1, 2, 3, 4, 5, 6]

// Alternativa funcional com array_reduce:
$flat2 = array_reduce($nested, fn(array $acc, array $sub): array => [...$acc, ...$sub], []);
print_r($flat2); // [1, 2, 3, 4, 5, 6]
```

---

## Iteração com `foreach`

### Sintaxe Básica

```php
<?php

// Apenas valores
$colors = ['vermelho', 'verde', 'azul'];
foreach ($colors as $color) {
    echo $color . PHP_EOL;
}

// Chave e valor
$user = ['name' => 'Maria', 'email' => 'maria@email.com', 'age' => 28];
foreach ($user as $key => $value) {
    echo "{$key}: {$value}" . PHP_EOL;
}
/*
nome: Maria
email: maria@email.com
idade: 28
*/
```

### Modificação por Referência no `foreach`

```php
<?php

$numbers = [1, 2, 3, 4, 5];

foreach ($numbers as &$value) {
    $value *= 2;
}
unset($value); // IMPORTANTE: liberar a referência

print_r($numbers); // [2, 4, 6, 8, 10]
```

⚠️ **Cuidado:** Sempre faça `unset($valor)` após modificar por referência em `foreach`. Caso contrário, a variável `$valor` permanece como referência para o último elemento, causando bugs sutis:

```php
<?php

$items = [1, 2, 3];
foreach ($items as &$value) { $value *= 10; }
// unset($value); // esquecido!

foreach ($items as $value) {
    // $value ainda é referência para $items[2]!
    // Isso corrompe o array original.
}
print_r($items); // resultado inesperado sem o unset
```

### Uso de `else` com `foreach`

```php
<?php

$empty = [];

foreach ($empty as $item) {
    echo $item;
} else {
    echo 'Array está vazio!';
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

## 📚 Referências

- [Documentação oficial: Arrays](https://www.php.net/manual/pt_BR/language.types.array.php)
- [Funções de Array](https://www.php.net/manual/pt_BR/ref.array.php)
- [array_map()](https://www.php.net/manual/pt_BR/function.array-map.php)
- [array_filter()](https://www.php.net/manual/pt_BR/function.array-filter.php)
- [array_reduce()](https://www.php.net/manual/pt_BR/function.array-reduce.php)
- [array_find() — PHP 8.4](https://www.php.net/manual/pt_BR/function.array-find.php)
- [array_find_key() — PHP 8.4](https://www.php.net/manual/pt_BR/function.array-find-key.php)
- [array_any() — PHP 8.4](https://www.php.net/manual/pt_BR/function.array-any.php)
- [array_all() — PHP 8.4](https://www.php.net/manual/pt_BR/function.array-all.php)
- [array_first() — PHP 8.5](https://www.php.net/manual/pt_BR/function.array-first.php)
- [array_last() — PHP 8.5](https://www.php.net/manual/pt_BR/function.array-last.php)
- [Spread Operator em Arrays](https://www.php.net/manual/pt_BR/migration74.new-features.php#migration74.new-features.core.spread-operator)
- [Array Destructuring](https://www.php.net/manual/pt_BR/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring)
- [Ordenação de Arrays](https://www.php.net/manual/pt_BR/array.sorting.php)
- [Estruturas de controle: foreach](https://www.php.net/manual/pt_BR/control-structures.foreach.php)

---

> **Capítulo anterior:** [06 — Funções](06-funcoes.md)
> **Próximo capítulo:** [08 — Strings](08-strings.md)
