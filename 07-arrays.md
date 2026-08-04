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

$frutas = ['Maçã', 'Banana', 'Laranja', 'Uva'];

echo $frutas[0];   // Maçã
echo $frutas[1];   // Banana
echo $frutas[2];   // Laranja
```

### Arrays Associativos

Usam chaves personalizadas (strings) para mapear valores:

```php
<?php

$usuario = [
    'nome'   => 'Maria Silva',
    'email'  => 'maria@email.com',
    'idade'  => 28,
    'ativo'  => true,
];

echo $usuario['nome'];   // Maria Silva
echo $usuario['email'];  // maria@email.com
```

### Arrays Mistos

É possível ter chaves numéricas e string no mesmo array:

```php
<?php

$dados = [
    'id'      => 42,
    'nome'    => 'Produto X',
    0         => 'primeiro',
    1         => 'segundo',
    'preco'   => 99.90,
];

echo $dados[0];        // primeiro
echo $dados['preco'];  // 99.9
```

💡 **Dica:** Em PHP, `$array['1']` e `$array[1]` referenciam **a mesma chave**, pois o PHP converte chaves numéricas em string para inteiro.

---

## Criação: array() vs Short Syntax

Prefira **sempre** a short syntax `[]`, introduzida no PHP 5.4. Ambas são idênticas:

```php
<?php

// Sintaxe antiga (evitar):
$lista = array(1, 2, 3);

// Sintaxe moderna (preferida):
$lista = [1, 2, 3];
```

### Chaves Automáticas

Se você não especificar uma chave, o PHP atribui a chave como o maior índice inteiro usado + 1:

```php
<?php

$itens = ['a', 'b', 7 => 'c', 'd', 'e'];

print_r($itens);
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
    'porta' => 3306,
];

echo $config['host'];                // localhost

// Acesso seguro com null coalescing (PHP 7+)
echo $config['timeout'] ?? 30;       // 30 (chave não existe, usa default)

// null coalescing em cadeia
echo $config['banco']['mysql']['porta'] ?? 5432; // 5432
```

### Escrita e Atualização

```php
<?php

$pessoa = ['nome' => 'João'];

$pessoa['idade'] = 25;         // adiciona nova chave
$pessoa['nome']  = 'João S.';  // atualiza valor existente
$pessoa[]        = 'extra';     // adiciona com índice automático (0)

print_r($pessoa);
/*
Array
(
    [nome] => João S.
    [idade] => 25
    [0] => extra
)
*/
```

### `unset()` — Remover Elementos

```php
<?php

$cores = ['vermelho', 'verde', 'azul', 'amarelo'];
unset($cores[1]);                         // remove 'verde'

print_r($cores);
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

$cores = array_values($cores);
print_r($cores);
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

$pilha = ['A', 'B'];
array_push($pilha, 'C', 'D', 'E');

print_r($pilha); // ['A', 'B', 'C', 'D', 'E']

// Equivalente mais performático:
$pilha[] = 'F';
```

💡 **Dica:** Para adicionar um único elemento, `$arr[] = $valor` é mais rápido que `array_push()`, pois não envolve overhead de chamada de função.

### `array_pop()` — Remover do Final

```php
<?php

$pilha = ['A', 'B', 'C'];
$ultimo = array_pop($pilha);  // remove e retorna 'C'

echo $ultimo;                 // C
print_r($pilha);              // ['A', 'B']
```

### `array_shift()` — Remover do Início

```php
<?php

$fila = ['primeiro', 'segundo', 'terceiro'];
$atendido = array_shift($fila);  // remove e retorna 'primeiro'

echo $atendido;                   // primeiro
print_r($fila);                   // ['segundo', 'terceiro']
```

⚠️ **Cuidado:** `array_shift()` reindexa todas as chaves numéricas, o que é custoso para arrays grandes (O(n)).

### `array_unshift()` — Adicionar ao Início

```php
<?php

$fila = ['B', 'C'];
array_unshift($fila, 'A');

print_r($fila); // ['A', 'B', 'C']
```

---

## Manipulação: merge, combine, slice, splice

### `array_merge()` — Mesclar Arrays

Combina dois ou mais arrays. Chaves string são sobrescritas; chaves numéricas são reindexadas:

```php
<?php

$defaults = ['host' => 'localhost', 'porta' => 3306, 'timeout' => 30];
$userConfig = ['host' => '192.168.1.10', 'usuario' => 'admin', 'timeout' => 60];

$final = array_merge($defaults, $userConfig);
print_r($final);
/*
Array
(
    [host] => 192.168.1.10    <- sobrescrito
    [porta] => 3306
    [timeout] => 60           <- sobrescrito
    [usuario] => admin
)
*/
```

```php
<?php

// Merge de arrays indexados
$a = [10, 20, 30];
$b = [40, 50];
$c = array_merge($a, $b);

print_r($c); // [10, 20, 30, 40, 50]
```

### `array_combine()` — Combinar Chaves e Valores

Cria um array associativo usando um array para chaves e outro para valores:

```php
<?php

$chaves = ['nome', 'email', 'idade'];
$valores = ['Carlos', 'carlos@email.com', 32];

$usuario = array_combine($chaves, $valores);
print_r($usuario);
/*
Array
(
    [nome] => Carlos
    [email] => carlos@email.com
    [idade] => 32
)
*/
```

⚠️ **Cuidado:** Ambos os arrays devem ter o mesmo número de elementos, senão o PHP lança um `ValueError` (PHP 8.0+).

### `array_slice()` — Extrair Fatia

Extrai uma porção do array **sem modificar o original**:

```php
<?php

$numeros = [10, 20, 30, 40, 50, 60];

$fatia = array_slice($numeros, 2, 3);     // a partir do índice 2, pega 3 elementos
print_r($fatia);                           // [30, 40, 50]

print_r(array_slice($numeros, -2));        // últimos 2: [50, 60]
print_r(array_slice($numeros, 0, 4));      // primeiros 4: [10, 20, 30, 40]
```

O quarto parâmetro (`preserve_keys`) mantém as chaves originais:

```php
<?php

$dados = [5 => 'a', 10 => 'b', 15 => 'c'];
print_r(array_slice($dados, 1, 2, true));
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

$cores = ['vermelho', 'verde', 'azul', 'amarelo', 'roxo'];

// Remove 2 elementos a partir do índice 2
$removidos = array_splice($cores, 2, 2);
print_r($removidos); // ['azul', 'amarelo']
print_r($cores);     // ['vermelho', 'verde', 'roxo']
```

Inserir elementos:

```php
<?php

$frutas = ['maçã', 'banana', 'laranja'];
array_splice($frutas, 1, 0, ['uva', 'pera']);  // insere na posição 1, remove 0

print_r($frutas); // ['maçã', 'uva', 'pera', 'banana', 'laranja']
```

---

## Busca em Arrays

### `in_array()` — Verificar se Valor Existe

```php
<?php

$frutas = ['maçã', 'banana', 'laranja'];

var_dump(in_array('banana', $frutas));   // bool(true)
var_dump(in_array('uva', $frutas));      // bool(false)

// Com verificação estrita de tipo (terceiro parâmetro):
$numeros = [1, 2, '3'];
var_dump(in_array(3, $numeros));         // bool(true)  — coerção!
var_dump(in_array(3, $numeros, true));   // bool(false) — estrito
```

### `array_key_exists()` — Verificar se Chave Existe

```php
<?php

$usuario = ['nome' => 'Ana', 'email' => 'ana@email.com'];

var_dump(array_key_exists('nome', $usuario));    // bool(true)
var_dump(array_key_exists('idade', $usuario));   // bool(false)

// Diferente de isset():
$dados = ['valor' => null];
var_dump(array_key_exists('valor', $dados)); // bool(true)
var_dump(isset($dados['valor']));            // bool(false) — NULL é considerado "não setado"
```

### `array_search()` — Encontrar Chave por Valor

```php
<?php

$cores = [
    'primaria'   => 'vermelho',
    'secundaria' => 'azul',
    'terciaria'  => 'verde',
];

$chave = array_search('azul', $cores);
echo $chave; // secundaria

// Se não encontrar:
$naoExiste = array_search('roxo', $cores);
var_dump($naoExiste); // bool(false)
```

⚠️ **Cuidado:** `array_search()` retorna `false` se não encontrar, mas também pode retornar `0` (índice zero, que é falsy). Sempre compare com `=== false`:

```php
<?php

$valores = [0 => 'primeiro', 1 => 'segundo'];
$resultado = array_search('primeiro', $valores);

if ($resultado === false) {
    echo 'Não encontrado';
} else {
    echo "Encontrado no índice: {$resultado}"; // Encontrado no índice: 0
}
```

---

## Programação Funcional com Arrays

### `array_map()` — Transformar Cada Elemento

Aplica uma função callback a cada elemento e retorna um novo array:

```php
<?php

$numeros = [1, 2, 3, 4, 5];

$dobrados = array_map(fn(int $n): int => $n * 2, $numeros);
print_r($dobrados); // [2, 4, 6, 8, 10]

// Com múltiplos arrays (PHP processa em paralelo):
$a = [10, 20, 30];
$b = [1, 2, 3];
$somas = array_map(fn(int $x, int $y): int => $x + $y, $a, $b);
print_r($somas); // [11, 22, 33]

// Com first-class callable (PHP 8.1+):
$nomes = [' ana ', ' Carlos ', ' BIA '];
$limpos = array_map(trim(...), $nomes);
print_r($limpos); // ['ana', 'Carlos', 'BIA']
```

💡 **Dica:** Se o callback for `null`, `array_map()` junta os arrays passados em um array multidimensional (comportamento similar a `array_merge` nas posições).

### `array_filter()` — Filtrar Elementos

Filtra elementos com base em um callback que retorna `true`:

```php
<?php

$numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$pares = array_filter($numeros, fn(int $n): bool => $n % 2 === 0);
print_r($pares); // [1 => 2, 3 => 4, 5 => 6, 7 => 8, 9 => 10]

// Manter apenas strings não vazias:
$dados = ['PHP', '', '8.5', false, null, '2026'];
$validos = array_filter($dados);
print_r($dados); // ['PHP', '8.5', '2026']
```

Sem callback, `array_filter()` remove valores falsy (`false`, `null`, `0`, `''`, `[]`).

### `array_reduce()` — Reduzir a um Único Valor

Acumula valores:

```php
<?php

$numeros = [1, 2, 3, 4, 5];

$soma = array_reduce($numeros, fn(int $acumulador, int $atual): int => $acumulador + $atual, 0);
echo $soma; // 15

// Calcular o produto de todos os elementos:
$produto = array_reduce($numeros, fn(int $acc, int $n): int => $acc * $n, 1);
echo $produto; // 120 (5!)

// Construir uma estrutura complexa (ex: agrupamento):
$pedidos = [
    ['cliente' => 'Ana',  'total' => 150.00],
    ['cliente' => 'João', 'total' => 200.00],
    ['cliente' => 'Ana',  'total' => 75.00],
    ['cliente' => 'João', 'total' => 50.00],
];

$porCliente = array_reduce(
    $pedidos,
    function (array $acc, array $pedido): array {
        $cliente = $pedido['cliente'];
        $acc[$cliente] = ($acc[$cliente] ?? 0) + $pedido['total'];
        return $acc;
    },
    [],
);

print_r($porCliente);
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

$valores = [10.5, 20.3, 30.7, 40.1];

array_walk($valores, function (float &$valor, int $indice): void {
    $valor = round($valor);
});

print_r($valores); // [11, 20, 31, 40]
```

Com callback que recebe argumento adicional:

```php
<?php

$precos = [100, 200, 300];
$imposto = 0.1; // 10%

array_walk($precos, function (float &$preco, int $indice, float $taxa): void {
    $preco += $preco * $taxa;
}, $imposto);

print_r($precos); // [110, 220, 330]
```

---

## Novas Funções de Busca (PHP 8.4+)

### `array_find()` — PHP 8.4+

Retorna o **primeiro elemento** que satisfaz o callback:

```php
<?php

$usuarios = [
    ['nome' => 'Ana',   'ativo' => true,  'pontos' => 120],
    ['nome' => 'João',  'ativo' => false, 'pontos' => 200],
    ['nome' => 'Maria', 'ativo' => true,  'pontos' => 85],
    ['nome' => 'Pedro', 'ativo' => true,  'pontos' => 150],
];

$primeiroInativo = array_find($usuarios, fn(array $u): bool => !$u['ativo']);
print_r($primeiroInativo);
/*
Array
(
    [nome] => João
    [ativo] => false
    [pontos] => 200
)
*/

// Se nenhum elemento for encontrado, retorna null
$inexistente = array_find($usuarios, fn(array $u): bool => $u['pontos'] > 999);
var_dump($inexistente); // NULL
```

### `array_find_key()` — PHP 8.4+

Retorna a **chave** do primeiro elemento que satisfaz o callback:

```php
<?php

$estoque = [
    'P001' => ['nome' => 'Notebook',   'qtd' => 0],
    'P002' => ['nome' => 'Mouse',      'qtd' => 45],
    'P003' => ['nome' => 'Teclado',    'qtd' => 0],
    'P004' => ['nome' => 'Monitor',    'qtd' => 12],
];

$primeiroZerado = array_find_key($estoque, fn(array $p): bool => $p['qtd'] === 0);
echo $primeiroZerado; // P001

// Não encontrado retorna null
$chave = array_find_key($estoque, fn(array $p): bool => $p['qtd'] > 100);
var_dump($chave); // NULL
```

### `array_any()` — PHP 8.4+

Verifica se **pelo menos um** elemento satisfaz o callback (semelhante a `some()` do JavaScript):

```php
<?php

$valores = [2, 4, 6, 7, 8, 10];

$temImpar = array_any($valores, fn(int $n): bool => $n % 2 !== 0);
var_dump($temImpar); // bool(true)

$todosNegativos = array_any($valores, fn(int $n): bool => $n < 0);
var_dump($todosNegativos); // bool(false)

// Exemplo prático: verificar se há usuários premium
$usuarios = [
    ['plano' => 'basic'],
    ['plano' => 'basic'],
    ['plano' => 'premium'],
];

$temPremium = array_any($usuarios, fn(array $u): bool => $u['plano'] === 'premium');
var_dump($temPremium); // bool(true)
```

### `array_all()` — PHP 8.4+

Verifica se **todos** os elementos satisfazem o callback (semelhante a `every()` do JavaScript):

```php
<?php

$idades = [18, 25, 30, 22, 19];

$todosMaiores = array_all($idades, fn(int $idade): bool => $idade >= 18);
var_dump($todosMaiores); // bool(true)

$todosPares = array_all($idades, fn(int $idade): bool => $idade % 2 === 0);
var_dump($todosPares); // bool(false)

// Exemplo prático: todas as senhas atendem ao critério mínimo
$senhas = ['ABcd1234!', 'XYzw5678@', 'PQrs9012#'];
$todasSeguras = array_all(
    $senhas,
    fn(string $s): bool => strlen($s) >= 8 && preg_match('/[A-Z]/', $s) && preg_match('/\d/', $s),
);
var_dump($todasSeguras); // bool(true)
```

💡 **Dica:** `array_any()` e `array_all()` são **short-circuit** — param de percorrer o array assim que a condição é determinada.

---

## Funções de Acesso Avançado

### `array_key_first()` e `array_key_last()` (PHP 7.3+)

```php
<?php

$dados = ['a' => 1, 'b' => 2, 'c' => 3];

echo array_key_first($dados); // a
echo array_key_last($dados);  // c

// Equivalente sem a função (menos eficiente):
// array_key_first: array_keys($dados)[0] ?? null
```

### `array_first()` e `array_last()` — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Estas funções retornam o **primeiro** ou **último valor** do array sem a necessidade de fazer `reset()` / `end()` ou `$arr[array_key_first($arr)]`:

```php
<?php

$numeros = ['a' => 10, 'b' => 20, 'c' => 30, 'd' => 40];

$primeiro = array_first($numeros);
$ultimo   = array_last($numeros);

echo $primeiro; // 10
echo $ultimo;   // 40

// Com array vazio, retornam null:
$vazio = [];
var_dump(array_first($vazio)); // NULL
var_dump(array_last($vazio));  // NULL
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

$usuario = ['nome' => 'Ana', 'email' => 'ana@email.com', 'idade' => 28];

$chaves = array_keys($usuario);
print_r($chaves); // ['nome', 'email', 'idade']

$valores = array_values($usuario);
print_r($valores); // ['Ana', 'ana@email.com', 28]

// array_keys com filtro de valor:
$numeros = [10, 20, 10, 30, 10, 40];
$indicesDo10 = array_keys($numeros, 10);
print_r($indicesDo10); // [0, 2, 4]
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

$numeros = [30, 10, 50, 20, 40];

sort($numeros);
print_r($numeros); // [10, 20, 30, 40, 50]

rsort($numeros);
print_r($numeros); // [50, 40, 30, 20, 10]
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

$dados = [
    'zebra' => 1,
    'alpha' => 2,
    'gama'  => 3,
    'beta'  => 4,
];

ksort($dados);
print_r($dados);
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

$produtos = [
    ['nome' => 'Notebook',  'preco' => 3500.00],
    ['nome' => 'Mouse',     'preco' => 89.90],
    ['nome' => 'Teclado',   'preco' => 199.90],
    ['nome' => 'Monitor',   'preco' => 1200.00],
];

// Ordenar por preço crescente
usort($produtos, fn(array $a, array $b): int => $a['preco'] <=> $b['preco']);

print_r($produtos);
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

$pessoas = [
    ['nome' => 'Ana',   'idade' => 30],
    ['nome' => 'João',  'idade' => 25],
    ['nome' => 'Maria', 'idade' => 30],
    ['nome' => 'Pedro', 'idade' => 25],
];

usort($pessoas, function (array $a, array $b): int {
    // Primeiro por idade, depois por nome
    return $a['idade'] <=> $b['idade']
        ?: $a['nome'] <=> $b['nome'];
});

print_r($pessoas);
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

$coordenadas = [10, 20, 30];

[$x, $y, $z] = $coordenadas;
echo "x={$x}, y={$y}, z={$z}"; // x=10, y=20, z=30

// Pular elementos:
[,, $terceiro] = $coordenadas;
echo $terceiro; // 30
```

### Destructuring Associativo (PHP 7.1+)

```php
<?php

$usuario = [
    'nome'    => 'Carlos',
    'email'   => 'carlos@email.com',
    'cidade'  => 'São Paulo',
];

['nome' => $nome, 'email' => $email] = $usuario;
echo $nome;  // Carlos
echo $email; // carlos@email.com
```

### Com foreach

```php
<?php

$alunos = [
    ['id' => 1, 'nome' => 'Ana',  'nota' => 9.5],
    ['id' => 2, 'nome' => 'João', 'nota' => 7.0],
    ['id' => 3, 'nome' => 'Bia',  'nota' => 8.5],
];

foreach ($alunos as ['nome' => $nome, 'nota' => $nota]) {
    echo "{$nome}: {$nota}" . PHP_EOL;
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

$admin = ['nome' => 'Admin', 'role' => 'admin'];
$dados = ['email' => 'admin@site.com', 'ativo' => true];

$usuario = [...$admin, ...$dados];
print_r($usuario);
/*
Array
(
    [nome] => Admin
    [role] => admin
    [email] => admin@site.com
    [ativo] => 1
)
*/
```

### Combinando Arrays Indexados

```php
<?php

$turmaA = ['Ana', 'João', 'Maria'];
$turmaB = ['Pedro', 'Bia'];
$turmaC = ['Carlos'];

$todas = [...$turmaA, ...$turmaB, ...$turmaC];
print_r($todas); // ['Ana', 'João', 'Maria', 'Pedro', 'Bia', 'Carlos']
```

### Adicionando Elementos no Meio

```php
<?php

$original = ['primeiro', 'quarto'];
$novo = [$original[0], 'segundo', 'terceiro', $original[1]];
// alternativa com spread:
$novo = [$original[0], ...['segundo', 'terceiro'], $original[1]];

print_r($novo); // ['primeiro', 'segundo', 'terceiro', 'quarto']
```

⚠️ **Cuidado:** Para arrays associativos, se houver chaves duplicadas, a **última** prevalece (comportamento similar a `array_merge`):

```php
<?php

$a = ['x' => 1, 'y' => 2];
$b = ['y' => 200, 'z' => 3];

$c = [...$a, ...$b];
print_r($c); // ['x' => 1, 'y' => 200, 'z' => 3]
```

---

## Arrays Multidimensionais

Arrays podem conter outros arrays como valores, criando **matrizes** ou estruturas hierárquicas:

```php
<?php

$matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

echo $matriz[1][2]; // 6 (linha 1, coluna 2)

// Percorrendo com foreach aninhado:
foreach ($matriz as $linha => $valores) {
    foreach ($valores as $coluna => $valor) {
        echo "[{$linha}][{$coluna}] = {$valor}" . PHP_EOL;
    }
}
```

### Estruturas de Dados Complexas

```php
<?php

$ecommerce = [
    'categorias' => [
        [
            'id'     => 1,
            'nome'   => 'Eletrônicos',
            'produtos' => [
                ['id' => 101, 'nome' => 'Smartphone',   'preco' => 2500.00, 'estoque' => 50],
                ['id' => 102, 'nome' => 'Notebook',     'preco' => 4500.00, 'estoque' => 20],
            ],
        ],
        [
            'id'     => 2,
            'nome'   => 'Livros',
            'produtos' => [
                ['id' => 201, 'nome' => 'PHP Moderno',  'preco' => 89.90,  'estoque' => 100],
                ['id' => 202, 'nome' => 'Clean Code',   'preco' => 120.00, 'estoque' => 75],
            ],
        ],
    ],
];

// Valor total do estoque de Eletrônicos
$totalEletronicos = array_reduce(
    $ecommerce['categorias'][0]['produtos'],
    fn(float $soma, array $p): float => $soma + ($p['preco'] * $p['estoque']),
    0.0,
);
echo "Total em estoque (Eletrônicos): R$ " . number_format($totalEletronicos, 2, ',', '.');
// Total em estoque (Eletrônicos): R$ 215.000,00
```

### Aplanar Array Multidimensional

```php
<?php

$aninhado = [[1, 2], [3, 4], [5, 6]];

$plano = array_merge(...$aninhado);
print_r($plano); // [1, 2, 3, 4, 5, 6]

// Alternativa funcional com array_reduce:
$plano2 = array_reduce($aninhado, fn(array $acc, array $sub): array => [...$acc, ...$sub], []);
print_r($plano2); // [1, 2, 3, 4, 5, 6]
```

---

## Iteração com `foreach`

### Sintaxe Básica

```php
<?php

// Apenas valores
$cores = ['vermelho', 'verde', 'azul'];
foreach ($cores as $cor) {
    echo $cor . PHP_EOL;
}

// Chave e valor
$usuario = ['nome' => 'Maria', 'email' => 'maria@email.com', 'idade' => 28];
foreach ($usuario as $chave => $valor) {
    echo "{$chave}: {$valor}" . PHP_EOL;
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

$numeros = [1, 2, 3, 4, 5];

foreach ($numeros as &$valor) {
    $valor *= 2;
}
unset($valor); // IMPORTANTE: liberar a referência

print_r($numeros); // [2, 4, 6, 8, 10]
```

⚠️ **Cuidado:** Sempre faça `unset($valor)` após modificar por referência em `foreach`. Caso contrário, a variável `$valor` permanece como referência para o último elemento, causando bugs sutis:

```php
<?php

$itens = [1, 2, 3];
foreach ($itens as &$v) { $v *= 10; }
// unset($v); // esquecido!

foreach ($itens as $v) {
    // $v ainda é referência para $itens[2]!
    // Isso corrompe o array original.
}
print_r($itens); // resultado inesperado sem o unset
```

### Uso de `else` com `foreach`

```php
<?php

$vazio = [];

foreach ($vazio as $item) {
    echo $item;
} else {
    echo 'Array está vazio!';
}
```

### Iteração com Objetos `ArrayIterator`

```php
<?php

$dados = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);

foreach ($dados as $chave => $valor) {
    echo "{$chave} => {$valor}" . PHP_EOL;
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
