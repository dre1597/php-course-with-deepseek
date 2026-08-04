# 04 — Operadores

## Operadores Aritméticos

Os operadores aritméticos realizam operações matemáticas básicas:

```php
<?php

$a = 10;
$b = 3;

echo $a + $b;   // 13  — Adição
echo $a - $b;   // 7   — Subtração
echo $a * $b;   // 30  — Multiplicação
echo $a / $b;   // 3.3333333333333 — Divisão (sempre retorna float, exceto divisão exata por inteiros)
echo $a % $b;   // 1   — Módulo (resto da divisão)
echo $a ** $b;  // 1000 — Exponenciação (PHP 5.6+): 10³
```

### Divisão e módulo em detalhes

```php
<?php

// Divisão sempre retorna float (exceto se a divisão for inteira exata dentro do range)
var_dump(10 / 2);   // int(5) — divisão exata
var_dump(10 / 3);   // float(3.3333333333333)

// Módulo com floats (PHP 8.0+ aceita)
var_dump(10.5 % 2.8);   // 2.1 → 10.5 - (3 * 2.8) = 10.5 - 8.4 = 2.1
var_dump(fmod(10.5, 2.8)); // 2.1 — função fmod() para compatibilidade

// Exponenciação: $a ** $b (PHP 5.6+)
echo 2 ** 8;    // 256
echo 2 ** 0;    // 1
echo 2 ** -1;   // 0.5 (equivalente a 1/2)
```

### Operador de negação e identidade

```php
<?php

$a = 10;

echo -$a;   // -10  — Negação
echo +$a;   // 10   — Identidade (não altera)
echo - -$a; // 10   — Dupla negação = valor original
```

---

## Operadores de Atribuição

```php
<?php

$a = 10;     // Atribuição simples

$a += 5;     // $a = $a + 5    → 15
$a -= 3;     // $a = $a - 3    → 12
$a *= 2;     // $a = $a * 2    → 24
$a /= 4;     // $a = $a / 4    → 6
$a %= 4;     // $a = $a % 4    → 2
$a **= 3;    // $a = $a ** 3   → 8
$a .= "abc"; // $a = $a . "abc" → "8abc" (concatenação)
```

### Encadeamento de atribuições

```php
<?php

// Atribuição em cadeia
$a = $b = $c = 42;
echo $a; // 42
echo $b; // 42
echo $c; // 42

// Cuidado: ordem de avaliação é da direita para a esquerda
$i = 1;
$j = ($i += 5) * 2;  // $i vira 6, $j = 6 * 2 = 12
echo "i={$i}, j={$j}"; // i=6, j=12
```

---

## Operadores de Comparação

```php
<?php

$a = 5;
$b = "5";
$c = 10;

// Igualdade (com coerção de tipo)
var_dump($a == $b);  // bool(true) — valores iguais após coerção
var_dump($a == $c);  // bool(false)

// Idêntico (mesmo valor E mesmo tipo)
var_dump($a === $b); // bool(false) — int(5) !== string("5")
var_dump($a === 5);  // bool(true)

// Diferente (com coerção)
var_dump($a != $b);  // bool(false)
var_dump($a <> $b);  // bool(false) — mesmo que !=, menos comum

// Não idêntico
var_dump($a !== $b); // bool(true) — tipos diferentes
var_dump($a !== 5);  // bool(false)

// Menor, maior, menor-ou-igual, maior-ou-igual
var_dump($a < $c);   // bool(true)
var_dump($a > $c);   // bool(false)
var_dump($a <= 5);   // bool(true)
var_dump($a >= 6);   // bool(false)
```

### Tabela de comparação com coerção (`==`)

```php
<?php

// Alguns resultados surpreendentes do == (comparação frouxa):

var_dump(0 == "0");          // true
var_dump(0 == "");           // true  ← cuidado!
var_dump(0 == "zero");       // false (PHP 8.0+: string não-numérica não é 0)
var_dump(0 == null);         // true  ← cuidado!
var_dump(0 == false);        // true
var_dump(0 == []);           // false (PHP 8.0+)
var_dump("0" == false);      // true
var_dump("0" == null);       // false
var_dump(null == false);     // true  ← cuidado!
var_dump("" == false);       // true
var_dump("" == null);        // true
var_dump([] == false);       // false (PHP 8.0+)
var_dump([] == null);        // true  ← cuidado!
var_dump([] == 0);           // false (PHP 8.0+)
var_dump(42 == true);        // true
var_dump(0 == false);        // true
var_dump(-1 == true);        // true
```

> ⚠️ **Cuidado**: Sempre prefira `===` (comparação estrita) sobre `==`.
> `==` pode produzir resultados inesperados devido à coerção automática de tipos.
> A regra prática: use `===` e `!==` como padrão.

### Operador Spaceship (`<=>`) — PHP 7.0+

Retorna `-1`, `0` ou `1` quando o operando da esquerda é menor, igual ou maior que o da direita:

```php
<?php

echo 1 <=> 1;   // 0  — iguais
echo 1 <=> 2;   // -1 — esquerda menor
echo 2 <=> 1;   // 1  — esquerda maior

echo "a" <=> "b"; // -1 — comparação de strings (ordem alfabética)
echo "b" <=> "a"; // 1
echo "a" <=> "a"; // 0

// Muito útil em funções de ordenação
$numeros = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numeros, fn($a, $b) => $a <=> $b);
print_r($numeros); // [1, 1, 2, 3, 4, 5, 6, 9]

// Ordenação por múltiplos critérios
$pessoas = [
    ['nome' => 'Ana',   'idade' => 30],
    ['nome' => 'Bob',   'idade' => 25],
    ['nome' => 'Carlos','idade' => 30],
    ['nome' => 'Diana', 'idade' => 25],
];

usort($pessoas, function(array $a, array $b): int {
    // Ordena por idade, e dentro da mesma idade por nome
    return $a['idade'] <=> $b['idade']
        ?: $a['nome'] <=> $b['nome'];
});

print_r($pessoas);
/*
[
    ['nome' => 'Bob',    'idade' => 25],
    ['nome' => 'Diana',  'idade' => 25],
    ['nome' => 'Ana',    'idade' => 30],
    ['nome' => 'Carlos', 'idade' => 30],
]
*/
```

---

## Operadores Lógicos

```php
<?php

$a = true;
$b = false;

// E lógico
var_dump($a && $b);  // false
var_dump($a and $b); // false (precedência MAIS BAIXA que &&)

// OU lógico
var_dump($a || $b);  // true
var_dump($a or $b);  // true (precedência MAIS BAIXA que ||)

// NÃO lógico
var_dump(!$a);       // false
var_dump(!$b);       // true

// XOR (OU exclusivo lógico)
var_dump($a xor $b); // true  — um true, outro false
var_dump($a xor true); // false — ambos true
var_dump(false xor false); // false — ambos false
```

### Diferença de precedência: `&&` vs `and`, `||` vs `or`

```php
<?php

// && tem precedência MAIOR que = 
$resultado = true && false;
var_dump($resultado); // bool(false) — interpretado como: $resultado = (true && false)

// and tem precedência MENOR que =
$resultado = true and false;
var_dump($resultado); // bool(true)! — interpretado como: ($resultado = true) and false

// || vs or — mesmo comportamento de precedência
$a = false || true;
var_dump($a); // bool(true) — ($a = (false || true))

$b = false or true;
var_dump($b); // bool(false) — (($b = false) or true)
```

> ⚠️ **Cuidado**: Evite `and` e `or` em expressões. Use sempre `&&` e `||`.
> A diferença de precedência é uma das maiores fontes de bugs em PHP.

### Curto-circuito (short-circuit evaluation)

```php
<?php

// Com &&: se o primeiro operando é false, o segundo NÃO é avaliado
function a(): bool { echo "A "; return false; }
function b(): bool { echo "B "; return true; }

$resultado = a() && b(); // Exibe apenas "A " — b() nunca é chamada
echo $resultado ? 'true' : 'false'; // false

echo "\n";

// Com ||: se o primeiro operando é true, o segundo NÃO é avaliado
$resultado = b() || a(); // Exibe apenas "B " — a() nunca é chamada
echo $resultado ? 'true' : 'false'; // true
```

### Aproveitando curto-circuito

```php
<?php

// Padrão comum: só executa se a variável estiver definida
$config = null;
$dbHost = $config && $config['db'] && $config['db']['host'];
// Nunca dá erro de array access em null, porque para no primeiro false

// Checagem de arquivo antes de incluir
$arquivo = 'config.php';
$carregado = file_exists($arquivo) && require $arquivo;

// Usar valor padrão
$nome = $_GET['nome'] ?? 'Visitante'; // Null coalescing (melhor)
// Antigo:
$nome = isset($_GET['nome']) ? $_GET['nome'] : 'Visitante';
// Ou com ||
$nome = $_GET['nome'] or $nome = 'Visitante'; // Funciona pelo curto-circuito, mas não claro
```

---

## Operadores de Incremento/Decremento

```php
<?php

$a = 5;

// Pré-incremento: incrementa ANTES de retornar
echo ++$a;   // 6 — $a é incrementado, depois retornado
echo $a;     // 6

// Pós-incremento: retorna ANTES de incrementar
echo $a++;   // 6 — retorna o valor atual, depois incrementa
echo $a;     // 7

// Pré-decremento
echo --$a;   // 6

// Pós-decremento
echo $a--;   // 6
echo $a;     // 5
```

```php
<?php

// Exemplo prático com loops
$i = 0;
while ($i++ < 5) {
    echo "Loop pós-incremento: {$i}\n";
}
// Loop pós-incremento: 1
// Loop pós-incremento: 2
// ...até 5

$i = 0;
while (++$i < 5) {
    echo "Loop pré-incremento: {$i}\n";
}
// Loop pré-incremento: 1
// ...até 4 (porque ++$i vira 5, e 5 < 5 é false)
```

### Incremento com caracteres

```php
<?php

$letra = 'A';
echo ++$letra; // B
echo ++$letra; // C
echo ++$letra; // D

// Vai até Z e continua
$letra = 'Z';
echo ++$letra; // AA
echo ++$letra; // AB

// Funciona com múltiplos caracteres
$letra = 'A99';
echo ++$letra; // B00
```

> ⚠️ **Cuidado**: Decremento com caracteres (`$letra--`) **não funciona**.
> Só o incremento funciona com strings.

---

## Operadores de String

### Concatenação (`.`)

```php
<?php

$nome    = "Maria";
$sobrenome = "Silva";

$nomeCompleto = $nome . " " . $sobrenome;
echo $nomeCompleto; // Maria Silva

// Concatenação com outros tipos
echo "Idade: " . 30;              // Idade: 30
echo "Preço: R$ " . 19.99;        // Preço: R$ 19.99
echo "Ativo: " . var_export(true); // Ativo: true
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

// Construindo SQL de forma dinâmica (com cuidado!)
$tabela = "usuarios";
$campos = ['nome', 'email', 'idade'];
$sql = "SELECT " . implode(', ', $campos) . " FROM {$tabela}";
$sql .= " WHERE ativo = 1";
$sql .= " ORDER BY nome ASC";
$sql .= " LIMIT 10";

echo $sql;
// SELECT nome, email, idade FROM usuarios WHERE ativo = 1 ORDER BY nome ASC LIMIT 10

// ⚠️ Para queries reais, use prepared statements com PDO, NUNCA concatene input do usuário!
```

---

## Operador Ternário

```php
<?php

// Sintaxe: condição ? valor_se_verdadeiro : valor_se_falso
$idade = 20;
$status = $idade >= 18 ? "Maior de idade" : "Menor de idade";
echo $status; // Maior de idade
```

### Ternário aninhado (evite)

```php
<?php

$nota = 7.5;

// Funciona, mas é ilegível — EVITE!
$conceito = $nota >= 9 ? 'A' : ($nota >= 7 ? 'B' : ($nota >= 5 ? 'C' : 'D'));

// Melhor: use match (PHP 8.0+)
$conceito = match (true) {
    $nota >= 9 => 'A',
    $nota >= 7 => 'B',
    $nota >= 5 => 'C',
    default    => 'D',
};

echo $conceito; // B
```

### Ternário curto (Elvis operator — PHP 5.3+)

```php
<?php

// Se o primeiro operando for truthy, usa ele; senão, usa o segundo
$nome = $_GET['nome'] ?: 'Visitante';

// Equivalente a (mas não exatamente igual):
$nome = $_GET['nome'] ? $_GET['nome'] : 'Visitante';

// Exemplo com valores falsy
$contador = 0;
$resultado = $contador ?: 10;
echo $resultado; // 10 — porque 0 é falsy

// Cuidado: se 0 for um valor válido, use null coalescing em vez disso
```

---

## Null Coalescing (`??`) — PHP 7.0+

```php
<?php

// Retorna o primeiro operando definido e não-null
$nome = $_GET['nome'] ?? 'Visitante';
// Se $_GET['nome'] existe e não é null, usa ele; senão, 'Visitante'

// Útil com arrays, objetos e valores que podem ser null
$config = ['db_host' => 'localhost', 'db_port' => null];

$host = $config['db_host'] ?? '127.0.0.1';
echo $host; // localhost — existe e não é null

$port = $config['db_port'] ?? 3306;
echo $port; // 3306 — existe mas é null, então usa o default

$user = $config['db_user'] ?? 'root';
echo $user; // root — não existe, então usa o default
```

### Encadeamento de `??`

```php
<?php

// PHP 7.4+: encadeia múltiplos ?? para testar várias fontes
$nome = $_GET['nome'] ?? $_POST['nome'] ?? $_COOKIE['nome'] ?? 'Anônimo';

// Testa $_GET['nome'], depois $_POST['nome'], depois $_COOKIE['nome'],
// e finalmente usa 'Anônimo'
```

### `??=` (Null coalescing assignment) — PHP 7.4+

```php
<?php

// Atribui apenas se a variável for null ou não estiver definida
$nome = 'João';
$nome ??= 'Visitante';
echo $nome; // João — já tinha valor, não altera

unset($nome);
$nome ??= 'Visitante';
echo $nome; // Visitante — não estava definida

$config = [];
$config['host'] ??= 'localhost';
echo $config['host']; // localhost
```

### Diferença entre `??` e `?:` e ternário

```php
<?php

$valor = 0;

// Elvis (?:) — verifica truthiness: 0 é falsy, então usa 'padrão'
echo $valor ?: 'padrão'; // padrão

// Null coalescing (??) — verifica apenas isset + não-null: 0 está definido e não é null
echo $valor ?? 'padrão'; // 0

// Ternário tradicional
echo $valor ? $valor : 'padrão'; // padrão — mesmo comportamento do elvis
```

| Operador      | PHP   | Verifica               | Exemplo `$x = 0`     | Exemplo `$x = null`  |
|---------------|-------|------------------------|----------------------|----------------------|
| `?:` (Elvis)  | 5.3+  | Truthy/falsy           | `'padrão'`           | `'padrão'`           |
| `??`          | 7.0+  | isset + não-null       | `0`                  | `'padrão'`           |
| `??=`         | 7.4+  | isset + não-null       | Não altera           | Atribui              |

---

## Nullsafe Operator (`?->`) — PHP 8.0+

Permite acessar propriedades e métodos de objetos que podem ser `null` sem verificação manual:

```php
<?php

class Endereco
{
    public function __construct(
        public string $rua,
        public ?Cidade $cidade = null,
    ) {}
}

class Cidade
{
    public function __construct(
        public string $nome,
        public ?Estado $estado = null,
    ) {}
}

class Estado
{
    public function __construct(
        public string $sigla,
    ) {}
}

$endereco = new Endereco(
    'Rua das Flores',
    new Cidade('São Paulo', new Estado('SP'))
);

// Sem nullsafe (verificação manual):
$sigla = null;
if ($endereco->cidade !== null && $endereco->cidade->estado !== null) {
    $sigla = $endereco->cidade->estado->sigla;
}

// Com nullsafe:
$sigla = $endereco->cidade?->estado?->sigla;
echo $sigla; // SP

// Se qualquer parte da cadeia for null, o resultado é null
$enderecoSemCidade = new Endereco('Av. Central', null);
$sigla = $enderecoSemCidade->cidade?->estado?->sigla;
var_dump($sigla); // NULL — não lança erro!
```

```php
<?php

// Nullsafe com métodos
class Usuario
{
    public function getPerfil(): ?Perfil
    {
        return null; // Simulando: usuário sem perfil
    }
}

class Perfil
{
    public function getAvatar(): string
    {
        return '/images/avatar.jpg';
    }
}

$usuario = new Usuario();

// Sem nullsafe
$avatar = $usuario->getPerfil() !== null
    ? $usuario->getPerfil()->getAvatar()
    : '/images/default.jpg';

// Com nullsafe + null coalescing
$avatar = $usuario->getPerfil()?->getAvatar() ?? '/images/default.jpg';
echo $avatar; // /images/default.jpg
```

---

## Operador Pipe (`|>`) — PHP 8.5+

> 🆕 **PHP 8.5+**: O operador pipe (`|>`) permite encadear chamadas de função
> passando o resultado da expressão anterior como argumento.

```php
<?php
// PHP 8.5+

// Sem pipe: código aninhado de difícil leitura
$resultado = array_reverse(array_unique(array_map('strtoupper', $palavras)));

// Com pipe: fluxo linear e legível
$resultado = $palavras
    |> array_map('strtoupper', $$)
    |> array_unique($$)
    |> array_reverse($$);

// $$ é a "placeholder variable" que recebe o valor do pipe anterior
```

### Exemplos detalhados com pipe

```php
<?php
// PHP 8.5+

// Processamento de dados com pipe
$dados = "  Maria Silva,28,São Paulo\n João Santos,35,Rio de Janeiro\n  Ana Costa,22,Belo Horizonte  ";

$usuarios = $dados
    |> trim($$)                          // Remove espaços das bordas
    |> explode("\n", $$)                 // Divide em linhas
    |> array_map('trim', $$)             // Limpa cada linha
    |> array_filter($$, 'strlen')        // Remove linhas vazias
    |> array_map(                        // Transforma cada linha em array associativo
        fn(string $linha): array => (
            sscanf($linha, '%[^,],%d,%s')
            |> ['nome' => $$[0], 'idade' => $$[1], 'cidade' => $$[2]]
        ),
        $$,
    );

print_r($usuarios);
/*
[
    ['nome' => 'Maria Silva', 'idade' => 28, 'cidade' => 'São Paulo'],
    ['nome' => 'João Santos', 'idade' => 35, 'cidade' => 'Rio de Janeiro'],
    ['nome' => 'Ana Costa', 'idade' => 22, 'cidade' => 'Belo Horizonte'],
]
*/
```

```php
<?php
// PHP 8.5+

// Pipe com operações matemáticas
function adicionarImposto(float $valor, float $taxa = 0.1): float
{
    return $valor * (1 + $taxa);
}

function aplicarDesconto(float $valor, float $desconto): float
{
    return $valor * (1 - $desconto);
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

$precoBase = 100.00;

$precoFinal = $precoBase
    |> adicionarImposto($$, 0.15)   // R$ 115.00
    |> adicionarImposto($$, 0.05)   // R$ 120.75 (imposto adicional)
    |> aplicarDesconto($$, 0.10)    // R$ 108.675
    |> round($$, 2)                 // R$ 108.68
    |> formatarMoeda($$);           // "R$ 108,68"

echo $precoFinal; // R$ 108,68
```

```php
<?php
// PHP 8.5+

// Pipe em pipelines de validação/transformação
function validarEmail(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException("Email inválido: {$email}");
    }
    return $email;
}

function normalizarEmail(string $email): string
{
    return strtolower(trim($email));
}

function sanitizar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Pipeline de processamento de input do usuário
$input = ' Joao@Exemplo.COM ';

try {
    $emailLimpo = $input
        |> sanitizar($$)
        |> normalizarEmail($$)
        |> validarEmail($$);

    echo "Email processado: {$emailLimpo}"; // Email processado: joao@exemplo.com
} catch (\InvalidArgumentException $e) {
    echo "Erro: " . $e->getMessage();
}
```

> 💡 **Dica**: O operador pipe é uma das maiores novidades do PHP 8.5.
> Ele elimina callbacks muito aninhados e torna pipelines de
> processamento de dados muito mais legíveis, similar ao operador `|>`
> do Elixir, F#, ou à proposta TC39 do JavaScript.

---

## Operadores de Array

### União (`+`)

```php
<?php

$padrao = [
    'host'     => 'localhost',
    'port'     => 3306,
    'charset'  => 'utf8mb4',
];

$usuario = [
    'host'     => 'db.producao.com',
    'username' => 'admin',
];

// União: mantém os valores do array da ESQUERDA quando há chaves duplicadas
$config = $usuario + $padrao;

print_r($config);
/*
[
    'host'     => 'db.producao.com',  // do $usuario (esquerda tem prioridade)
    'username' => 'admin',             // do $usuario
    'port'     => 3306,                // do $padrao (não existia no $usuario)
    'charset'  => 'utf8mb4',           // do $padrao
]
*/
```

> ⚠️ **Cuidado**: Não confunda `+` com `array_merge()`:
> - `$a + $b`: chaves de `$a` têm prioridade
> - `array_merge($a, $b)`: chaves de `$b` sobrescrevem `$a` (para chaves string)
>
> Para arrays indexados, `array_merge()` reindexa e concatena; `+` ignora
> índices já existentes na esquerda.

```php
<?php

$a = [1, 2, 3];
$b = [4, 5, 6, 7];

print_r($a + $b);        // [1, 2, 3, 7] — índices 0,1,2 já existem em $a
print_r(array_merge($a, $b)); // [1, 2, 3, 4, 5, 6, 7] — concatena tudo
```

### Comparação de arrays

```php
<?php

$a = ['maçã', 'banana'];
$b = [0 => 'maçã', 1 => 'banana'];
$c = ['banana', 'maçã'];

var_dump($a == $b);   // true — mesmos pares chave/valor
var_dump($a === $b);  // true — mesma ordem e mesmo tipo
var_dump($a == $c);   // false — valores diferentes nas posições
var_dump($a != $c);   // true
```

---

## Operadores Bitwise

```php
<?php

$a = 0b1100;  // 12
$b = 0b1010;  // 10

// E bit a bit: ambos os bits precisam ser 1
printf("%b\n", $a & $b);   // 1000 → 8

// OU bit a bit: pelo menos um bit precisa ser 1
printf("%b\n", $a | $b);   // 1110 → 14

// XOR bit a bit: bits diferentes = 1
printf("%b\n", $a ^ $b);   // 0110 → 6

// NÃO bit a bit: inverte todos os bits (cuidado com o sinal)
printf("%b\n", ~$a);       // ...11110011 → -13

// Deslocamento à esquerda
printf("%b\n", $a << 1);   // 11000 → 24 (multiplica por 2)
printf("%b\n", $a << 2);   // 110000 → 48 (multiplica por 4)

// Deslocamento à direita
printf("%b\n", $a >> 1);   // 110 → 6 (divide por 2)
printf("%b\n", $a >> 2);   // 11 → 3 (divide por 4)
```

### Aplicações práticas de bitwise

```php
<?php

// Flags de permissão como bits (padrão comum)
const PODE_LER    = 1;    // 0b0001
const PODE_ESCREVER = 2;  // 0b0010
const PODE_DELETAR  = 4;  // 0b0100
const PODE_ADMIN    = 8;  // 0b1000

// Combinando permissões
$permissoesUsuario = PODE_LER | PODE_ESCREVER; // 3 (0b0011)
$permissoesAdmin   = PODE_LER | PODE_ESCREVER | PODE_DELETAR | PODE_ADMIN; // 15 (0b1111)

// Verificando permissões
if ($permissoesUsuario & PODE_ESCREVER) {
    echo "Usuário pode escrever\n";
}

if (!($permissoesUsuario & PODE_DELETAR)) {
    echo "Usuário NÃO pode deletar\n";
}

// Adicionando permissão
$permissoesUsuario |= PODE_DELETAR; // Agora tem permissão de deletar

// Removendo permissão
$permissoesUsuario &= ~PODE_DELETAR; // Remove permissão de deletar

// Toggle (liga/desliga)
$permissoesUsuario ^= PODE_ESCREVER; // Se tinha, remove; se não tinha, adiciona
```

---

## Operador `instanceof`

Verifica se uma variável é instância de uma classe, subclasse ou implementa uma interface:

```php
<?php

class Animal {}
class Cachorro extends Animal {}
interface Voador {}

$dog = new Cachorro();

var_dump($dog instanceof Cachorro); // true
var_dump($dog instanceof Animal);   // true — é subclasse
var_dump($dog instanceof Voador);   // false — não implementa a interface

// Com strings (PHP 8.0+ permite instanceof com string):
$classe = 'Cachorro';
var_dump($dog instanceof $classe); // true

// Com variáveis soltas
$valor = 42;
var_dump($valor instanceof \DateTime); // false

// Com null (sempre retorna false)
$nulo = null;
var_dump($nulo instanceof \DateTime); // false
```

---

## 📚 Precedência de operadores (tabela resumida)

Da mais alta para a mais baixa precedência:

| Precedência | Operadores                                           |
|-------------|------------------------------------------------------|
| 1           | `clone`, `new`                                       |
| 2           | `**` (exponenciação)                                 |
| 3           | `++`, `--`, `~`, `(int)`, `(float)`, `(string)`, etc |
| 4           | `instanceof`                                         |
| 5           | `!` (negação lógica)                                 |
| 6           | `*`, `/`, `%`                                        |
| 7           | `+`, `-`, `.`                                        |
| 8           | `<<`, `>>`                                           |
| 9           | `<`, `<=`, `>`, `>=`                                 |
| 10          | `==`, `!=`, `===`, `!==`, `<>`, `<=>`                |
| 11          | `&` (bitwise AND)                                    |
| 12          | `^` (bitwise XOR)                                    |
| 13          | `\|` (bitwise OR)                                    |
| 14          | `&&` (AND lógico)                                    |
| 15          | `\|\|` (OR lógico)                                   |
| 16          | `??` (null coalescing)                               |
| 17          | `?:` (ternário)                                      |
| 18          | `=`, `+=`, `-=`, `.=`, `??=`, etc                    |
| 19          | `and`                                                |
| 20          | `xor`                                                |
| 21          | `or`                                                 |
| 22          | `\|>` (pipe, PHP 8.5+)                               |

> 💡 **Dica**: Na dúvida sobre precedência, **use parênteses**. Código explícito
> é melhor que código que depende de memorização da tabela de precedência.

```php
<?php

// Exemplo onde parênteses salvam
$resultado = true ? 'sim' : 'não' . ' obrigado';
echo $resultado; // "sim" — o ternário tem precedência menor que concatenação?

// Com parênteses fica claro
$resultado = (true ? 'sim' : 'não') . ' obrigado';
echo $resultado; // "sim obrigado"
```

---

## 📚 Referências

- **Operadores aritméticos**: [php.net/manual/pt_BR/language.operators.arithmetic.php](https://www.php.net/manual/pt_BR/language.operators.arithmetic.php)
- **Operadores de comparação**: [php.net/manual/pt_BR/language.operators.comparison.php](https://www.php.net/manual/pt_BR/language.operators.comparison.php)
- **Operadores lógicos**: [php.net/manual/pt_BR/language.operators.logical.php](https://www.php.net/manual/pt_BR/language.operators.logical.php)
- **Operadores de string**: [php.net/manual/pt_BR/language.operators.string.php](https://www.php.net/manual/pt_BR/language.operators.string.php)
- **Operadores de array**: [php.net/manual/pt_BR/language.operators.array.php](https://www.php.net/manual/pt_BR/language.operators.array.php)
- **Operadores bitwise**: [php.net/manual/pt_BR/language.operators.bitwise.php](https://www.php.net/manual/pt_BR/language.operators.bitwise.php)
- **Precedência de operadores**: [php.net/manual/pt_BR/language.operators.precedence.php](https://www.php.net/manual/pt_BR/language.operators.precedence.php)
- **Null coalescing**: [php.net/manual/pt_BR/migration70.new-features.php](https://www.php.net/manual/pt_BR/migration70.new-features.php#migration70.new-features.null-coalesce-op)
- **Nullsafe operator**: [php.net/manual/pt_BR/migration80.new-features.php](https://www.php.net/manual/pt_BR/migration80.new-features.php#migration80.new-features.nullsafe-operator)
- **PHP 8.5 Changelog**: [php.net/ChangeLog-8.php#PHP_8_5](https://www.php.net/ChangeLog-8.php#PHP_8_5)
- **RFC Pipe Operator**: [wiki.php.net/rfc/pipe-operator-v2](https://wiki.php.net/rfc/pipe-operator-v2)

---

## Próximo módulo

[→ 05 — Estruturas de Controle](./05-estruturas-de-controle.md)
