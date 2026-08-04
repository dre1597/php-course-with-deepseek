# 03 — Tipos de Dados e Variáveis

## Variáveis

### Declaração e uso

Toda variável em PHP começa com `$`:

```php
<?php

$nome = "Maria";
$idade = 30;
$altura = 1.72;
$ativo = true;
```

Nomes de variáveis são **case-sensitive**:

```php
<?php

$nome = "João";
$Nome = "Maria";
$NOME = "Carlos";

echo $nome; // João
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
$_variavel = 1;
$nome_usuario = "ana";
$total2 = 100;
$café = "quente";          // Unicode — funciona, mas evite
$variável = "válido";
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

$campo = "nome";
$$campo = "Beatriz";   // Cria $nome = "Beatriz"

echo $nome;            // Beatriz
echo $$campo;          // Beatriz
```

> ⚠️ **Cuidado**: Variáveis variáveis são confusas e quase nunca necessárias.
> Em 99% dos casos, use arrays associativos (`$dados[$campo]`).

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
$resultado = 0.1 + 0.2;
$esperado  = 0.3;

// Jeito errado:
if ($resultado === 0.3) { /* nunca executa */ }

// Jeito certo: usar epsilon
$epsilon = 0.00001;
if (abs($resultado - $esperado) < $epsilon) {
    echo "Iguais (com tolerância)";
}
```

### `bool` (booleano)

```php
<?php

$verdadeiro = true;
$falso = false;

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

foreach ($falsy as $valor) {
    echo var_export($valor, true) . ' → ' . var_export((bool)$valor, true) . "\n";
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

$nome = "Carlos";

// Aspas simples: NÃO interpreta variáveis nem escapes especiais
echo 'Olá, $nome!\n';  // Olá, $nome!\n

// Aspas duplas: interpreta variáveis e escapes (\n, \t, \\, \$)
echo "Olá, $nome!\n";  // Olá, Carlos! (com quebra de linha)
```

#### Interpolação de strings

```php
<?php

$fruta = "maçã";
$quantidade = 5;

// Simples
echo "Eu tenho $quantidade $fruta(s).";
// Eu tenho 5 maçã(s).

// Com chaves (recomendado para clareza)
echo "Eu tenho {$quantidade} {$fruta}(s).";

// Acessando arrays e objetos
$produto = ['nome' => 'Caneta', 'preco' => 2.50];
echo "Produto: {$produto['nome']} custa R\$ {$produto['preco']}";

class Item {
    public string $nome = 'Caderno';
}
$item = new Item();
echo "Item: {$item->nome}";

// Expressões dentro de chaves
echo "Total: {$quantidade * 2.50}";  // PHP 8.1+ ???
```

#### Heredoc

```php
<?php

$nome = "Mundo";
$versao = PHP_VERSION;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Olá, {$nome}!</title>
</head>
<body>
    <h1>Bem-vindo, {$nome}</h1>
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
    $usuario = "João";

    return <<<TEMPLATE
        Olá, {$usuario}!

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
$nome = "Maria";

$texto = <<<'TEXTO'
Olá, $nome!
Aqui não há interpolação de variáveis.
Nem escapes: \n \t
TEXTO;

echo $texto;
// Olá, $nome!
// Aqui não há interpolação de variáveis.
// Nem escapes: \n \t
```

---

## Declarações de tipo (Type Declarations)

A partir do PHP 7.0+, podemos declarar tipos para parâmetros de funções, retornos e propriedades.

### Tipos escalares em funções

```php
<?php

function somar(int $a, int $b): int
{
    return $a + $b;
}

echo somar(5, 3);    // 8
echo somar(5, "3");  // 8 (coerção: string "3" → int 3)
// somar(5, "abc");  // TypeError se strict_types=1
```

### Tipos em propriedades (PHP 7.4+ / 8.0+)

```php
<?php

class Produto
{
    public string $nome;
    public float $preco;
    public int $estoque;
    public bool $disponivel;

    public function __construct(
        string $nome,
        float $preco,
        int $estoque = 0
    ) {
        $this->nome = $nome;
        $this->preco = $preco;
        $this->estoque = $estoque;
        $this->disponivel = $estoque > 0;
    }
}
```

### Constructor property promotion (PHP 8.0+)

```php
<?php

class Produto
{
    public function __construct(
        public string $nome,
        public float $preco,
        public int $estoque = 0,
        public bool $disponivel = false,
    ) {
        $this->disponivel = $estoque > 0;
    }
}
```

---

## Tipos union e intersection

### Union types (PHP 8.0+)

Um parâmetro ou retorno pode aceitar múltiplos tipos:

```php
<?php

function formatarId(int|string $id): string
{
    return (string) $id;
}

echo formatarId(42);     // "42"
echo formatarId("ABC");  // "ABC"

// Retorno com union type
function buscarUsuario(string $id): ?array
{
    // ?array é açúcar sintático para array|null
    $usuarios = [
        '1' => ['nome' => 'Alice'],
        '2' => ['nome' => 'Bob'],
    ];
    return $usuarios[$id] ?? null;
}

// Union com mais de 2 tipos
function processar(mixed $valor): int|float|string
{
    return match (true) {
        is_int($valor)   => $valor * 2,
        is_float($valor) => round($valor, 2),
        is_string($valor) => strtoupper($valor),
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

interface TemNome
{
    public function getNome(): string;
}

interface TemPreco
{
    public function getPreco(): float;
}

class Produto implements TemNome, TemPreco
{
    public function __construct(
        private string $nome,
        private float $preco,
    ) {}

    public function getNome(): string { return $this->nome; }
    public function getPreco(): float { return $this->preco; }
}

class Servico implements TemNome, TemPreco
{
    public function __construct(
        private string $nome,
        private float $valorHora,
        private int $horas,
    ) {}

    public function getNome(): string { return $this->nome; }
    public function getPreco(): float { return $this->valorHora * $this->horas; }
}

// A função aceita QUALQUER objeto que implemente AMBAS as interfaces
function exibirPreco(TemNome& TemPreco $item): string
{
    return "{$item->getNome()}: R\$ " . number_format($item->getPreco(), 2, ',', '.');
}

$produto = new Produto('Teclado', 250.00);
$servico = new Servico('Consultoria', 150.00, 3);

echo exibirPreco($produto); // Teclado: R$ 250,00
echo exibirPreco($servico); // Consultoria: R$ 450,00
```

### Tipos nullable (`?Tipo`)

```php
<?php

function buscarPorId(int $id): ?string
{
    // ?string é equivalente a string|null
    if ($id === 0) {
        return null;
    }
    return "Registro #{$id}";
}

$resultado = buscarPorId(0);
var_dump($resultado); // NULL

$resultado = buscarPorId(42);
var_dump($resultado); // string(12) "Registro #42"
```

---

## Tipos especiais modernos

### `mixed` (PHP 8.0+)

Aceita qualquer tipo. É o tipo "coringa":

```php
<?php

function debug(mixed $valor): void
{
    var_dump($valor);
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

function logMensagem(string $msg): void
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

function redirecionar(string $url): never
{
    header("Location: {$url}");
    exit();
}

function erroFatal(string $mensagem): never
{
    throw new \RuntimeException($mensagem);
}

function tipoInvalido(): never
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
function obterConfig(string $chave): string|null
{
    // string|null ao invés de ?string (equivalente)
    $config = ['app' => 'MeuApp'];
    return $config[$chave] ?? null;
}
```

### `true` como tipo (PHP 8.2+)

```php
<?php

// PHP 8.2+ permite true como tipo (útil em union types)
interface Validavel
{
    public function validar(): true|string;
    // Retorna true se válido, ou string com mensagem de erro
}

class Email implements Validavel
{
    public function __construct(private string $valor) {}

    public function validar(): true|string
    {
        if (!filter_var($this->valor, FILTER_VALIDATE_EMAIL)) {
            return "Email '{$this->valor}' inválido";
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

function somar(int $a, int $b): int
{
    return $a + $b;
}

echo somar(5, 3);     // 8 — OK
echo somar(5, "3");   // TypeError! "3" não é int
```

```php
<?php

// Sem strict_types (padrão): coerção acontece
function somar(int $a, int $b): int
{
    return $a + $b;
}

echo somar(5, "3");   // 8 — a string "3" é convertida para int 3
echo somar(5, "3.7"); // 8 — float 3.7 → int 3 (perda de precisão!)
```

> 💡 **Dica**: **SEMPRE** use `declare(strict_types=1);` no topo de cada arquivo.
> Isso evita bugs silenciosos e torna o código mais previsível.

---

## Funções de inspeção de tipo

```php
<?php

$valor = 42;

// Obtém o tipo como string
echo gettype($valor);             // "integer"
echo get_debug_type($valor);      // "int" (PHP 8.0+, mais preciso)

// Verificações booleanas
var_dump(is_int($valor));         // bool(true)
var_dump(is_float($valor));       // bool(false)
var_dump(is_string($valor));      // bool(false)
var_dump(is_bool($valor));        // bool(false)
var_dump(is_array($valor));       // bool(false)
var_dump(is_object($valor));      // bool(false)
var_dump(is_null($valor));        // bool(false)
var_dump(is_numeric($valor));     // bool(true) — é número?
var_dump(is_scalar($valor));      // bool(true) — é escalar?
var_dump(is_callable($valor));    // bool(false)
var_dump(is_iterable($valor));    // bool(false)
var_dump(isset($valor));          // bool(true) — está definida e não é null?
var_dump(empty($valor));          // bool(false) — é falsy?

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

$valor = "123";
settype($valor, "int");
echo $valor;        // 123
var_dump($valor);   // int(123)

// Equivalente ao cast explícito:
$valor = (int) "123";
```

---

## Arrays — Introdução

### Arrays indexados

```php
<?php

// Sintaxe moderna (PHP 5.4+)
$frutas = ['maçã', 'banana', 'laranja', 'uva'];

// Sintaxe antiga (ainda válida)
$frutas = array('maçã', 'banana', 'laranja', 'uva');

echo $frutas[0];       // maçã
echo $frutas[2];       // laranja

$frutas[] = 'morango'; // Adiciona no final
echo $frutas[4];       // morango
```

### Arrays associativos

```php
<?php

$usuario = [
    'nome'      => 'Ana Carolina',
    'email'     => 'ana@email.com',
    'idade'     => 28,
    'admin'     => true,
];

echo $usuario['nome'];   // Ana Carolina
echo $usuario['email'];  // ana@email.com
```

### Arrays multidimensionais

```php
<?php

$produtos = [
    [
        'nome'  => 'Notebook',
        'preco' => 3500.00,
        'tags'  => ['eletrônicos', 'informática'],
    ],
    [
        'nome'  => 'Mouse',
        'preco' => 89.90,
        'tags'  => ['periféricos'],
    ],
];

echo $produtos[0]['nome'];         // Notebook
echo $produtos[1]['tags'][0];      // periféricos
```

Veremos arrays em profundidade em um módulo dedicado mais adiante.

---

## Enums (PHP 8.1+)

Enums permitem definir um conjunto fixo de valores possíveis.

### Enum puro (Pure Enum)

```php
<?php

enum StatusPedido
{
    case Pendente;
    case Pago;
    case Enviado;
    case Entregue;
    case Cancelado;
}

function atualizarStatus(StatusPedido $status): void
{
    echo "Status alterado para: " . $status->name . "\n";
}

atualizarStatus(StatusPedido::Pago);      // Status alterado para: Pago
atualizarStatus(StatusPedido::Entregue);  // Status alterado para: Entregue
```

### Backed Enum (com valor)

```php
<?php

enum TamanhoCamiseta: string
{
    case PP = 'pp';
    case P  = 'p';
    case M  = 'm';
    case G  = 'g';
    case GG = 'gg';
    case XG = 'xg';
}

function selecionarTamanho(TamanhoCamiseta $tamanho): void
{
    echo "Tamanho: " . $tamanho->value . "\n";
}

selecionarTamanho(TamanhoCamiseta::M); // Tamanho: m

// A partir de um valor:
$tamanho = TamanhoCamiseta::from('g');
echo $tamanho->name;  // G

// from() lança ValueError se o valor não existir:
// TamanhoCamiseta::from('xxl'); // ValueError

// tryFrom() retorna null se não existir:
$tentativa = TamanhoCamiseta::tryFrom('xxl');
var_dump($tentativa); // NULL
```

### Enums com inteiros

```php
<?php

enum CodigoErro: int
{
    case NaoEncontrado     = 404;
    case NaoAutorizado     = 401;
    case ErroInterno       = 500;
    case ValidacaoFalha    = 422;
}

echo CodigoErro::NaoEncontrado->value; // 404
```

### Métodos em enums

```php
<?php

enum MetodoPagamento: string
{
    case CartaoCredito = 'credit_card';
    case Boleto        = 'boleto';
    case Pix           = 'pix';
    case Debito        = 'debit_card';

    public function rotulo(): string
    {
        return match ($this) {
            self::CartaoCredito => 'Cartão de Crédito',
            self::Boleto        => 'Boleto Bancário',
            self::Pix           => 'PIX',
            self::Debito        => 'Cartão de Débito',
        };
    }

    public function prazoProcessamento(): string
    {
        return match ($this) {
            self::Pix           => 'Instantâneo',
            self::CartaoCredito => 'Até 24h',
            self::Debito        => 'Até 24h',
            self::Boleto        => 'Até 3 dias úteis',
        };
    }
}

$metodo = MetodoPagamento::Pix;
echo $metodo->rotulo();              // PIX
echo $metodo->prazoProcessamento();  // Instantâneo

// Iterar sobre todos os cases:
foreach (MetodoPagamento::cases() as $case) {
    echo "{$case->rotulo()} → {$case->value}\n";
}
```

### Enum com interface e trait

```php
<?php

interface Descrevivel
{
    public function descrever(): string;
}

trait DescricaoPadrao
{
    public function descrever(): string
    {
        return match (true) {
            $this instanceof StatusPedido => "Pedido {$this->name}",
            default => $this->name,
        };
    }
}

enum StatusPedidoComDescricao: string implements Descrevivel
{
    use DescricaoPadrao;

    case Pendente  = 'P';
    case Pago      = 'PG';
    case Enviado   = 'E';
    case Entregue  = 'ET';
}

$status = StatusPedidoComDescricao::Pendente;
echo $status->descrever();  // Pedido Pendente
```

---

## Type juggling (coerção de tipos)

O PHP converte tipos conforme o contexto:

```php
<?php

// String para int em contexto aritmético
$resultado = "10" + 5;
echo $resultado;          // 15
var_dump($resultado);     // int(15)

// Int para string em concatenação
$texto = "Total: " . 100;
echo $texto;              // Total: 100
var_dump($texto);         // string(10) "Total: 100"

// String para float
$soma = "1.5" + "2.5";
var_dump($soma);          // float(4)

// Casts explícitos
$inteiro   = (int) "42";       // 42
$float     = (float) "3.14";   // 3.14
$string    = (string) 100;     // "100"
$booleano  = (bool) 1;         // true
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
