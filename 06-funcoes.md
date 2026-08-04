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

function saudacao(): void
{
    echo "Olá, mundo!";
}

saudacao(); // Olá, mundo!
```

A partir do PHP 8.0, funções podem ser declaradas em qualquer ordem — o PHP resolve o símbolo antes da execução. No entanto, funções condicionais (declaradas dentro de `if`) só ficam disponíveis após a condição ser avaliada como verdadeira.

### Nomes de Função

Nomes de função são case-insensitive:

```php
<?php

function minhaFuncao(): void
{
    echo "executada";
}

MINHAFUNCAO(); // funciona, embora não seja recomendado
```

💡 **Dica:** Mantenha consistência no casing. O padrão PSR-12 recomenda `camelCase` para nomes de função.

---

## Parâmetros: Obrigatórios e Opcionais

Parâmetros são declarados entre os parênteses da função. Parâmetros com valor padrão (default) são opcionais e devem vir **depois** dos obrigatórios.

```php
<?php

function criarUsuario(string $nome, int $idade = 18, bool $ativo = true): array
{
    return [
        'nome'  => $nome,
        'idade' => $idade,
        'ativo' => $ativo,
    ];
}

// Chamadas válidas
$user1 = criarUsuario('João');                  // idade=18, ativo=true
$user2 = criarUsuario('Maria', 25);              // idade=25, ativo=true
$user3 = criarUsuario('Pedro', 30, false);       // idade=30, ativo=false
```

⚠️ **Cuidado:** Não é possível declarar um parâmetro obrigatório após um opcional. O PHP emitirá um erro fatal:

```php
<?php

// ERRO: parâmetro obrigatório $b vem depois do opcional $a
function errada(int $a = 1, int $b): void {}
```

### Valores Default com Expressões (PHP 8.1+)

Desde o PHP 8.1, valores padrão podem ser qualquer expressão escalar, incluindo `new`:

```php
<?php

function obterData(DateTimeInterface $data = new DateTimeImmutable('now')): string
{
    return $data->format('Y-m-d');
}

echo obterData(); // 2026-08-04 (data atual)
```

---

## Parâmetros Nomeados (Named Arguments)

**PHP 8.0+** — Você pode passar argumentos pelo nome do parâmetro, ignorando a ordem posicional:

```php
<?php

function criarPedido(
    string $produto,
    int $quantidade = 1,
    float $preco = 0.0,
    string $cliente = 'Anônimo',
): array {
    return compact('produto', 'quantidade', 'preco', 'cliente');
}

// Chamadas com named arguments
$pedido1 = criarPedido(
    produto: 'Notebook',
    preco: 3500.00,
    cliente: 'Ana',
    quantidade: 2,
);

$pedido2 = criarPedido(preco: 99.90, produto: 'Mouse');

print_r($pedido1);
/*
Array
(
    [produto] => Notebook
    [quantidade] => 2
    [preco] => 3500
    [cliente] => Ana
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

function multiplicar(int $a, int $b): int
{
    return $a * $b;
}

echo multiplicar(3, 4);    // 12
// multiplicar(3.5, 4);    // TypeError — float não é aceito como int
```

### Nullable Types

Prefixar com `?` permite que o parâmetro ou retorno seja do tipo especificado ou `null`:

```php
<?php

function buscarPorId(int $id): ?array
{
    $dados = [1 => ['nome' => 'João'], 2 => ['nome' => 'Maria']];
    return $dados[$id] ?? null;
}

$resultado = buscarPorId(3); // null
var_dump($resultado);        // NULL
```

---

## Union Types, Intersection Types, Mixed, Void, Never

### Union Types (PHP 8.0+)

Permitem que um parâmetro ou retorno aceite mais de um tipo:

```php
<?php

function formatarValor(int|float|string $valor): string
{
    if (is_numeric($valor)) {
        return number_format((float) $valor, 2, ',', '.');
    }
    return strtoupper((string) $valor);
}

echo formatarValor(1500);      // 1.500,00
echo formatarValor(99.9);      // 99,90
echo formatarValor('abc');     // ABC
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

class Pedido implements Logavel, Serializavel
{
    public function getLogMessage(): string
    {
        return 'Pedido processado';
    }

    public function toArray(): array
    {
        return ['id' => 1];
    }
}

function registrarESerializar(Logavel&Serializavel $entidade): array
{
    echo $entidade->getLogMessage() . PHP_EOL;
    return $entidade->toArray();
}

$pedido = new Pedido();
print_r(registrarESerializar($pedido));
```

### `mixed`

O tipo `mixed` indica que o valor pode ser de **qualquer tipo** — `null`, `bool`, `int`, `float`, `string`, `array` ou `object`. Introduzido no PHP 8.0 como tipo nativo:

```php
<?php

function processar(mixed $entrada): mixed
{
    if (is_array($entrada)) {
        return array_map(strtoupper(...), $entrada);
    }
    if (is_string($entrada)) {
        return strtoupper($entrada);
    }
    return $entrada;
}

var_dump(processar('hello'));        // string(5) "HELLO"
var_dump(processar(['a', 'b']));     // array(2) { [0]=> "A", [1]=> "B" }
var_dump(processar(42));             // int(42)
```

### `void`

Indica que a função **não retorna valor**. Qualquer tentativa de usar o retorno resulta em `null`, e declarar `return` com valor gera erro:

```php
<?php

function logMensagem(string $msg): void
{
    error_log($msg);
    // return $msg;  // Erro: função void não pode retornar valor
}

$resultado = logMensagem('teste');
var_dump($resultado); // NULL
```

### `never` (PHP 8.1+)

Indica que a função **nunca retorna** — ela sempre lança uma exceção, chama `exit()`/`die()`, ou entra em loop infinito:

```php
<?php

function abortar(int $codigo, string $mensagem = ''): never
{
    http_response_code($codigo);
    echo json_encode(['erro' => $mensagem]);
    exit;
}

function redirecionar(string $url): never
{
    header("Location: {$url}");
    exit;
}

// abortar(404, 'Página não encontrada');
```

⚠️ **Cuidado:** Se uma função declarada como `never` conseguir alcançar o fim do corpo sem lançar exceção ou interromper a execução, o PHP lançará um `TypeError`.

---

## Retorno de Valores

### `return` Simples

Toda função que não é `void` ou `never` deve retornar um valor compatível com seu type hint:

```php
<?php

function soma(int $a, int $b): int
{
    return $a + $b;
}
```

### Múltiplos Pontos de Retorno

É válido ter múltiplos `return` dentro de uma função:

```php
<?php

function classificarNota(float $nota): string
{
    if ($nota >= 9.0) {
        return 'A';
    }
    if ($nota >= 7.0) {
        return 'B';
    }
    if ($nota >= 5.0) {
        return 'C';
    }
    return 'F';
}

echo classificarNota(8.5); // B
```

### Retorno Condicional de Tipos (Union Types)

```php
<?php

function encontrar(array $dados, string $chave): int|string|null
{
    if (array_key_exists($chave, $dados)) {
        return $dados[$chave];
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

function calcular(): void
{
    $x = 10;        // escopo local
    echo $x;        // 10
}

calcular();
// echo $x;         // Warning: Undefined variable $x
```

### Palavra-Chave `global`

A palavra-chave `global` importa uma variável do escopo global para dentro da função:

```php
<?php

$contador = 0;

function incrementar(): void
{
    global $contador;
    $contador++;
}

incrementar();
incrementar();
echo $contador; // 2
```

### Array Superglobal `$GLOBALS`

Alternativa ao `global`, o array `$GLOBALS` contém todas as variáveis do escopo global:

```php
<?php

$total = 100;

function aplicarDesconto(float $percentual): void
{
    $GLOBALS['total'] -= $GLOBALS['total'] * ($percentual / 100);
}

aplicarDesconto(10);
echo $total; // 90
```

### Cláusula `use` em Closures

Para funções anônimas (closures), usa-se `use` para herdar variáveis do escopo pai:

```php
<?php

$multiplicador = 3;

$dobrar = function (int $valor) use ($multiplicador): int {
    return $valor * $multiplicador;
};

echo $dobrar(5); // 15
```

A herança por `use` é por valor. Para herdar por referência, prefixe com `&`:

```php
<?php

$acumulador = 0;

$somar = function (int $valor) use (&$acumulador): void {
    $acumulador += $valor;
};

$somar(10);
$somar(5);
echo $acumulador; // 15
```

---

## Funções Anônimas, Closures e Arrow Functions

### Funções Anônimas (Closures)

São funções sem nome, atribuíveis a variáveis, passáveis como argumento e retornáveis:

```php
<?php

$saudacao = function (string $nome): string {
    return "Olá, {$nome}!";
};

echo $saudacao('Ana'); // Olá, Ana!
```

### Closures como Callbacks

```php
<?php

$nomes = ['João', 'Maria', 'Pedro'];

$mapeados = array_map(function (string $nome): string {
    return strtoupper($nome);
}, $nomes);

print_r($mapeados); // ['JOÃO', 'MARIA', 'PEDRO']
```

### Arrow Functions `fn =>` (PHP 7.4+)

Sintaxe concisa para closures de uma única expressão. Herdam variáveis do escopo (por valor):

```php
<?php

$multiplicador = 2;
$valores = [1, 2, 3, 4, 5];

$resultado = array_map(fn(int $n): int => $n * $multiplicador, $valores);

print_r($resultado); // [2, 4, 6, 8, 10]
```

Arrow functions também suportam type hints:

```php
<?php

$formatar = fn(int|float $valor): string => number_format($valor, 2, ',', '.');

echo $formatar(1234.5); // 1.234,50
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
$nomes = ['ana', 'carlos', 'bia'];

// Antes:
$maiusculos = array_map('strtoupper', $nomes);

// Agora (PHP 8.1+):
$maiusculos = array_map(strtoupper(...), $nomes);

print_r($maiusculos); // ['ANA', 'CARLOS', 'BIA']
```

### Funciona com Métodos de Instância e Estáticos

```php
<?php

class Calculadora
{
    public function dobrar(int $n): int
    {
        return $n * 2;
    }

    public static function triplicar(int $n): int
    {
        return $n * 3;
    }
}

$calc = new Calculadora();

$dobrar = $calc->dobrar(...);          // método de instância
$triplicar = Calculadora::triplicar(...); // método estático

echo $dobrar(10);     // 20
echo $triplicar(10);  // 30
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

$tarefa = function (string $nome): void {
    $atual = Closure::getCurrent();
    // Reflection sobre a própria closure
    $ref = new ReflectionFunction($atual);
    echo "Executando a closure '{$ref->getName()}' com parâmetro: {$nome}" . PHP_EOL;
};

$tarefa('importar_dados');
// Executando a closure '{closure}' com parâmetro: importar_dados
```

Outro caso de uso — criar callbacks que se auto-referenciam:

```php
<?php

$contador = function (int $passo = 1) use (&$contador): void {
    static $valor = 0;
    $valor += $passo;
    echo "Contador: {$valor}" . PHP_EOL;

    if ($valor < 10) {
        $atual = Closure::getCurrent();
        $atual($passo);
    }
};

$contador(2);
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

function somarTudo(int ...$numeros): int
{
    return array_sum($numeros);
}

echo somarTudo(1, 2, 3);       // 6
echo somarTudo(10, 20, 30, 40); // 100
echo somarTudo();               // 0
```

### Combinando com Parâmetros Fixos

```php
<?php

function logComContexto(string $nivel, string $mensagem, mixed ...$contexto): void
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] [{$nivel}] {$mensagem}" . PHP_EOL;

    if (!empty($contexto)) {
        echo "Contexto: " . json_encode($contexto, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

logComContexto('ERROR', 'Falha na conexão', 'host' => 'db.local', 'porta' => 5432);
// [2026-08-04 10:30:00] [ERROR] Falha na conexão
// Contexto: {"host":"db.local","porta":5432}
```

### Desempacotamento de Argumentos

O operador spread também funciona ao **chamar** uma função, desempacotando um array em argumentos:

```php
<?php

function criarLinha(string $a, string $b, string $c): string
{
    return "{$a} | {$b} | {$c}";
}

$dados = ['PHP', '8.5', '2026'];
echo criarLinha(...$dados); // PHP | 8.5 | 2026
```

---

## Funções por Referência (`&$param`)

Prefixar um parâmetro com `&` faz com que a função possa modificar a variável original:

```php
<?php

function adicionarSufixo(string &$texto, string $sufixo): void
{
    $texto .= $sufixo;
}

$nome = 'PHP';
adicionarSufixo($nome, ' 8.5');
echo $nome; // PHP 8.5
```

### Retorno por Referência

```php
<?php

$config = ['debug' => false, 'cache' => true];

function &obterConfig(string $chave): mixed
{
    global $config;
    return $config[$chave];
}

$debug = &obterConfig('debug');
$debug = true;

var_dump($config['debug']); // bool(true)
```

⚠️ **Cuidado:** Use referências com moderação. Elas reduzem a legibilidade e podem causar efeitos colaterais inesperados. Prefira retornar novos valores em vez de modificar os originais.

---

## Funções Recursivas

Uma função que chama a si mesma é **recursiva**. Toda recursão precisa de uma **condição de parada**:

```php
<?php

function fatorial(int $n): int
{
    if ($n <= 1) {
        return 1;                   // condição de parada
    }
    return $n * fatorial($n - 1);    // chamada recursiva
}

echo fatorial(5); // 120 (5 × 4 × 3 × 2 × 1)
```

### Recursão com Array (Percorrer Estrutura em Árvore)

```php
<?php

function listarCategorias(array $categorias, int $nivel = 0): void
{
    foreach ($categorias as $cat) {
        echo str_repeat('  ', $nivel) . "- {$cat['nome']}" . PHP_EOL;
        if (!empty($cat['filhas'])) {
            listarCategorias($cat['filhas'], $nivel + 1);
        }
    }
}

$arvore = [
    [
        'nome'   => 'Eletrônicos',
        'filhas' => [
            ['nome' => 'Celulares', 'filhas' => []],
            ['nome' => 'Notebooks', 'filhas' => []],
        ],
    ],
    [
        'nome'   => 'Livros',
        'filhas' => [
            ['nome' => 'Ficção',    'filhas' => []],
            ['nome' => 'Técnicos',  'filhas' => []],
            ['nome' => 'Biografias', 'filhas' => []],
        ],
    ],
];

listarCategorias($arvore);
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
    public function emitirSom(): string
    {
        return 'som genérico';
    }
}

class Cachorro extends Animal
{
    #[\Override]
    public function emitirSom(): string
    {
        return 'au au';
    }
}

// Se a classe pai não tiver o método, #[\Override] causa erro fatal:
class Gato extends Animal
{
    // #[\Override]
    // public function miar(): string { return 'miau'; }
    // Erro: Gato::miar() não sobrescreve nenhum método
}
```

💡 **Dica:** Use `#[\Override]` sempre que sobrescrever métodos. Isso protege contra renomeações acidentais na classe pai e documenta a intenção.

### `#[\NoDiscard]` — PHP 8.5 NOVIDADE!

**PHP 8.5+** — O atributo `#[\NoDiscard]` indica que o valor de retorno de uma função **não deve ser ignorado**. Se o retorno for descartado, o PHP emitirá um `E_USER_NOTICE`:

```php
<?php

#[\NoDiscard]
function gerarToken(): string
{
    return bin2hex(random_bytes(32));
}

// Chamada correta:
$token = gerarToken();

// Chamada incorreta — o retorno é descartado, dispara notice:
// gerarToken();
// Notice: The return value of function gerarToken() should not be discarded

// Também funciona em métodos:
class BancoDeDados
{
    #[\NoDiscard]
    public function conectar(): self
    {
        // lógica de conexão
        return $this;
    }
}

$db = new BancoDeDados();
$db->conectar(); // Notice: retorno descartado

// Correto:
$db = (new BancoDeDados())->conectar();
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
