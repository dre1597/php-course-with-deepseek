# 05 — Estruturas de Controle

## `if`, `else`, `elseif`

A estrutura condicional mais fundamental:

```php
<?php

$idade = 20;

if ($idade >= 18) {
    echo "Maior de idade";
}
```

### `else`

```php
<?php

$temperatura = 15;

if ($temperatura >= 30) {
    echo "Muito quente";
} else {
    echo "Temperatura agradável";
}
```

### `elseif` / `else if`

```php
<?php

$hora = 14;

if ($hora < 6) {
    echo "Madrugada";
} elseif ($hora < 12) {
    echo "Manhã";
} elseif ($hora < 18) {
    echo "Tarde";
} elseif ($hora < 24) {
    echo "Noite";
} else {
    echo "Horário inválido";
}
```

`elseif` e `else if` são equivalentes em PHP; `elseif` (tudo junto) é mais idiomático.

### Condições com múltiplas expressões

```php
<?php

$usuarioLogado = true;
$temPermissao = false;

if ($usuarioLogado && $temPermissao) {
    echo "Acesso concedido ao painel admin";
} elseif ($usuarioLogado && !$temPermissao) {
    echo "Login OK, mas sem permissão de admin. Redirecionando...";
} else {
    echo "Faça login para continuar";
}
```

### Validação de entrada com `if`

```php
<?php

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$erros = [];

if ($email === '') {
    $erros[] = 'O email é obrigatório.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'Formato de email inválido.';
}

if ($senha === '') {
    $erros[] = 'A senha é obrigatória.';
} elseif (strlen($senha) < 8) {
    $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
}

if ($erros === []) {
    echo "Tudo validado! Prosseguindo...";
} else {
    foreach ($erros as $erro) {
        echo "Erro: {$erro}\n";
    }
}
```

---

## `switch`

`switch` compara uma expressão com múltiplos valores possíveis:

```php
<?php

$diaSemana = 3;

switch ($diaSemana) {
    case 1:
        echo "Domingo";
        break;
    case 2:
        echo "Segunda-feira";
        break;
    case 3:
        echo "Terça-feira";
        break;
    case 4:
        echo "Quarta-feira";
        break;
    case 5:
        echo "Quinta-feira";
        break;
    case 6:
        echo "Sexta-feira";
        break;
    case 7:
        echo "Sábado";
        break;
    default:
        echo "Dia inválido";
        break;
}
// Saída: Terça-feira
```

### Fall-through (sem `break`)

Sem `break`, a execução "cai" para o próximo `case`:

```php
<?php

$mes = 2;
$ano = 2024;

switch ($mes) {
    case 1:
    case 3:
    case 5:
    case 7:
    case 8:
    case 10:
    case 12:
        echo "31 dias";
        break;
    case 4:
    case 6:
    case 9:
    case 11:
        echo "30 dias";
        break;
    case 2:
        // Ano bissexto?
        $dias = ($ano % 4 === 0 && $ano % 100 !== 0) || ($ano % 400 === 0) ? 29 : 28;
        echo "{$dias} dias";
        break;
    default:
        echo "Mês inválido";
}
// Saída: 29 dias (2024 é bissexto)
```

### Comparação no `switch`

`switch` usa comparação **frouxa** (`==`), não estrita (`===`):

```php
<?php

$valor = 0;

switch ($valor) {
    case false:
        echo "Entrou no false"; // Executa este! Porque 0 == false
        break;
    case 0:
        echo "Entrou no 0";
        break;
}

// ⚠️ Cuidado: switch usa ==, não ===
// "0" matcha com 0, false matcha com 0, null matcha com 0...
```

> ⚠️ **Cuidado**: `switch` utiliza comparação frouxa (`==`). Para comparação estrita,
prefira `match` (PHP 8.0+).

### `switch` com múltiplos valores de entrada (alternativa)

Às vezes você quer avaliar condições complexas, não só valores fixos:

```php
<?php

// Técnica: switch(true) para condições complexas
$nota = 8.5;
$frequencia = 85; // porcentagem

switch (true) {
    case $nota >= 7 && $frequencia >= 75:
        echo "Aprovado";
        break;
    case $nota >= 5 && $frequencia >= 75:
        echo "Recuperação";
        break;
    case $frequencia < 75:
        echo "Reprovado por falta";
        break;
    default:
        echo "Reprovado por nota";
        break;
}
// Saída: Aprovado
```

---

## `match` — PHP 8.0+

`match` é uma expressão mais moderna, segura e concisa comparada ao `switch`:

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

$mensagem = match ($statusCode) {
    200     => 'OK',
    201     => 'Created',
    301     => 'Moved Permanently',
    404     => 'Not Found',
    500     => 'Internal Server Error',
    default => 'Unknown Status',
};

echo $mensagem; // Not Found
```

### Múltiplas condições para o mesmo braço

```php
<?php

$dia = 'sabado';

$tipoDia = match ($dia) {
    'segunda', 'terca', 'quarta', 'quinta', 'sexta' => 'Dia útil',
    'sabado', 'domingo' => 'Fim de semana',
    default => 'Dia inválido',
};

echo $tipoDia; // Fim de semana
```

### Expressões como corpo do braço

```php
<?php

$valor = 150.00;
$tipoDesconto = 'blackfriday';

$precoFinal = match ($tipoDesconto) {
    'vip'         => $valor * 0.7,           // 30% off
    'blackfriday' => $valor * 0.5,           // 50% off
    'assinante'   => $valor * 0.85,          // 15% off
    default       => $valor,                 // preço cheio
};

echo "Preço original: R$ " . number_format($valor, 2, ',', '.') . "\n";
echo "Preço final: R$ " . number_format($precoFinal, 2, ',', '.') . "\n";
// Preço original: R$ 150,00
// Preço final: R$ 75,00
```

### `match` com enums

```php
<?php

enum MetodoPagamento: string
{
    case Pix     = 'pix';
    case Boleto  = 'boleto';
    case Credito = 'credito';
    case Debito  = 'debito';
}

function obterTaxaProcessamento(MetodoPagamento $metodo): float
{
    return match ($metodo) {
        MetodoPagamento::Pix     => 0.00,   // sem taxa
        MetodoPagamento::Boleto  => 3.50,   // taxa fixa
        MetodoPagamento::Credito => 0.0499, // 4.99%
        MetodoPagamento::Debito  => 0.0299, // 2.99%
    };
    // Não precisa de default: enum cobre todos os casos
    // Se um novo case for adicionado ao enum, o PHP emitirá
    // um UnhandledMatchError em tempo de execução
}

echo obterTaxaProcessamento(MetodoPagamento::Pix); // 0.0
```

### `match(true)` para condições complexas

```php
<?php

$nota = 8.5;
$frequencia = 85;

$resultado = match (true) {
    $nota >= 9 && $frequencia >= 90  => 'Aprovado com louvor',
    $nota >= 7 && $frequencia >= 75  => 'Aprovado',
    $nota >= 5 && $frequencia >= 75  => 'Recuperação',
    $frequencia < 75                 => 'Reprovado por falta',
    default                          => 'Reprovado por nota',
};

echo $resultado; // Aprovado
```

### `match` sem `default` e `UnhandledMatchError`

Se nenhum braço corresponder e não houver `default`, o PHP lança `\UnhandledMatchError`:

```php
<?php

$direcao = 'norte';

try {
    $comando = match ($direcao) {
        'cima'    => 'Mover para cima',
        'baixo'   => 'Mover para baixo',
        'esquerda' => 'Mover para esquerda',
        'direita' => 'Mover para direita',
    };
} catch (\UnhandledMatchError $e) {
    echo "Direção '{$direcao}' não reconhecida.";
}
// Direção 'norte' não reconhecida.
```

---

## `while` e `do-while`

### `while`

Executa o bloco **enquanto** a condição for verdadeira. O PHP verifica a condição **antes** de cada iteração:

```php
<?php

$contador = 1;

while ($contador <= 5) {
    echo "Iteração {$contador}\n";
    $contador++;
}
// Iteração 1
// Iteração 2
// Iteração 3
// Iteração 4
// Iteração 5
```

```php
<?php

// Leitura de arquivo linha por linha com while
$handle = fopen('dados.csv', 'r');
if ($handle === false) {
    die('Não foi possível abrir o arquivo');
}

$linhaNumero = 0;
while (($linha = fgetcsv($handle)) !== false) {
    $linhaNumero++;
    echo "Linha {$linhaNumero}: " . implode(' | ', $linha) . "\n";
}
fclose($handle);
```

### `do-while`

A condição é verificada **depois** da execução. Garante que o bloco execute **pelo menos uma vez**:

```php
<?php

$tentativas = 0;
$maxTentativas = 3;

do {
    $tentativas++;
    echo "Tentativa {$tentativas} de {$maxTentativas}\n";

    // Simula uma operação que pode falhar
    $sucesso = random_int(0, 1) === 1;

    if ($sucesso) {
        echo "Operação concluída com sucesso!\n";
        break;
    }

    echo "Falhou. ";
} while ($tentativas < $maxTentativas);

if ($tentativas === $maxTentativas && !$sucesso) {
    echo "Todas as tentativas falharam.\n";
}
```

---

## `for`

```php
<?php

// for (inicialização; condição; incremento)
for ($i = 0; $i < 5; $i++) {
    echo "i = {$i}\n";
}
// i = 0
// i = 1
// i = 2
// i = 3
// i = 4
```

### Múltiplas variáveis no `for`

```php
<?php

// Contagem crescente e decrescente simultânea
for ($i = 0, $j = 10; $i <= 10; $i++, $j--) {
    echo "i = {$i}, j = {$j}, soma = " . ($i + $j) . "\n";
}
```

### `for` sem algumas expressões

```php
<?php

// Sem inicialização (variável inicializada fora)
$i = 0;
for (; $i < 5; $i++) {
    echo "{$i} ";
}
echo "\n";

// Sem incremento (feito dentro do bloco)
for ($i = 0; $i < 5;) {
    echo "{$i} ";
    $i++;
}
echo "\n";

// Loop infinito com for (todas as expressões vazias)
// for (;;) { ... } — equivalente a while(true) { ... }

// Sem condição (vira loop infinito — útil com break interno)
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

### Aplicação: tabuada

```php
<?php

echo "Tabuada do 7:\n";
echo str_repeat('─', 20) . "\n";

for ($multiplicador = 1; $multiplicador <= 10; $multiplicador++) {
    $resultado = 7 * $multiplicador;
    echo "7 × " . str_pad($multiplicador, 2, ' ', STR_PAD_LEFT) . " = " . str_pad($resultado, 2, ' ', STR_PAD_LEFT) . "\n";
}
/*
Tabuada do 7:
────────────────────
7 ×  1 =  7
7 ×  2 = 14
7 ×  3 = 21
...
7 × 10 = 70
*/
```

### Aplicação: gerar HTML com `for`

```php
<?php

$meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril',
    'Maio', 'Junho', 'Julho', 'Agosto',
    'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

echo "<select name='mes'>\n";
for ($i = 0; $i < count($meses); $i++) {
    $valor = $i + 1;
    $selecionado = ($valor === (int)date('m')) ? ' selected' : '';
    echo "    <option value='{$valor}'{$selecionado}>{$meses[$i]}</option>\n";
}
echo "</select>\n";
```

---

## `foreach`

A forma mais idiomática de iterar sobre arrays e objetos iteráveis em PHP.

### Apenas valores

```php
<?php

$frutas = ['maçã', 'banana', 'laranja', 'uva', 'morango'];

foreach ($frutas as $fruta) {
    echo "Fruta: {$fruta}\n";
}
```

### Chave e valor

```php
<?php

$usuario = [
    'nome'   => 'Carlos Eduardo',
    'email'  => 'carlos@exemplo.com',
    'idade'  => 34,
    'cidade' => 'Belo Horizonte',
];

foreach ($usuario as $chave => $valor) {
    echo ucfirst($chave) . ": {$valor}\n";
}
// Nome: Carlos Eduardo
// Email: carlos@exemplo.com
// Idade: 34
// Cidade: Belo Horizonte
```

### `foreach` com arrays aninhados

```php
<?php

$produtos = [
    ['nome' => 'Notebook',    'preco' => 3500.00, 'estoque' => 12],
    ['nome' => 'Monitor 27"', 'preco' => 1200.00, 'estoque' => 5],
    ['nome' => 'Teclado',     'preco' => 250.00,  'estoque' => 30],
    ['nome' => 'Mouse',       'preco' => 120.00,  'estoque' => 0],
];

echo "┌──────┬────────────────────┬──────────┬─────────┐\n";
echo "│ ID   │ Produto            │ Preço    │ Estoque │\n";
echo "├──────┼────────────────────┼──────────┼─────────┤\n";

foreach ($produtos as $id => $produto) {
    $idStr     = str_pad($id + 1, 4, ' ', STR_PAD_LEFT);
    $nomeStr   = str_pad($produto['nome'], 18, ' ');
    $precoStr  = 'R$ ' . str_pad(number_format($produto['preco'], 2, ',', '.'), 7, ' ', STR_PAD_LEFT);
    $estoqueStr = str_pad($produto['estoque'], 7, ' ', STR_PAD_LEFT);

    $status = $produto['estoque'] > 0 ? '' : ' (esgotado)';

    echo "│ {$idStr} │ {$nomeStr} │ {$precoStr} │ {$estoqueStr} │{$status}\n";
}

echo "└──────┴────────────────────┴──────────┴─────────┘\n";
```

### Modificando arrays com `foreach` (por referência)

```php
<?php

$numeros = [1, 2, 3, 4, 5];

// Por valor: NÃO altera o array original
foreach ($numeros as $num) {
    $num *= 2; // $num é uma cópia
}
print_r($numeros); // [1, 2, 3, 4, 5] — inalterado

// Por referência: ALTERA o array original
foreach ($numeros as &$num) {
    $num *= 2;
}
unset($num); // IMPORTANTE: desfazer a referência após o loop!
print_r($numeros); // [2, 4, 6, 8, 10]
```

> ⚠️ **Cuidado**: Sempre chame `unset()` na variável de referência após o loop.
> Caso contrário, a última referência permanece, causando bugs sutis:
>
> ```php
> <?php
> $arr = [1, 2, 3];
> foreach ($arr as &$v) { $v *= 2; }
> // Esqueceu o unset($v)!
> foreach ($arr as $v) { echo $v; }
> // Último elemento é sobrescrito inesperadamente!
> ```

### `foreach` em objetos iteráveis

```php
<?php

// Qualquer objeto que implemente Traversable pode ser usado em foreach

class Contador implements \Iterator
{
    private int $posicao = 0;
    private array $valores;

    public function __construct(array $valores)
    {
        $this->valores = array_values($valores);
    }

    public function current(): mixed { return $this->valores[$this->posicao]; }
    public function key(): mixed { return $this->posicao; }
    public function next(): void { $this->posicao++; }
    public function rewind(): void { $this->posicao = 0; }
    public function valid(): bool { return isset($this->valores[$this->posicao]); }
}

$contador = new Contador(['a', 'b', 'c']);
foreach ($contador as $indice => $valor) {
    echo "{$indice}: {$valor}\n";
}
```

### `foreach` com `$key => $value` em arrays indexados

```php
<?php

$cores = ['vermelho', 'verde', 'azul'];

foreach ($cores as $indice => $cor) {
    echo "[{$indice}] = {$cor}\n";
}
// [0] = vermelho
// [1] = verde
// [2] = azul
```

### `foreach` em strings (com `str_split`)

```php
<?php

$texto = "PHP";
$caracteres = mb_str_split($texto); // PHP 7.4+

foreach ($caracteres as $i => $char) {
    echo "Posição {$i}: {$char}\n";
}
// Posição 0: P
// Posição 1: H
// Posição 2: P
```

---

## `break` e `continue`

### `break`

Interrompe a execução do loop atual:

```php
<?php

echo "Procurando o número 7...\n";

$numeros = [1, 3, 5, 7, 9, 11, 13];

foreach ($numeros as $posicao => $numero) {
    echo "  Verificando posição {$posicao}: {$numero}\n";
    if ($numero === 7) {
        echo "  → Encontrado na posição {$posicao}!\n";
        break; // Para o loop imediatamente
    }
}
/*
Procurando o número 7...
  Verificando posição 0: 1
  Verificando posição 1: 3
  Verificando posição 2: 5
  Verificando posição 3: 7
  → Encontrado na posição 3!
*/
```

### `break N` (quebrando múltiplos níveis)

```php
<?php

$matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

$alvo = 5;
$encontrado = false;

foreach ($matriz as $linhaIndex => $linha) {
    foreach ($linha as $colunaIndex => $valor) {
        echo "  [{$linhaIndex}][{$colunaIndex}] = {$valor}\n";
        if ($valor === $alvo) {
            echo "  → Encontrado em [{$linhaIndex}][{$colunaIndex}]\n";
            break 2; // Sai de AMBOS os foreach
        }
    }
}
```

### `continue`

Pula para a próxima iteração do loop:

```php
<?php

echo "Números pares entre 1 e 10:\n";

for ($i = 1; $i <= 10; $i++) {
    if ($i % 2 !== 0) {
        continue; // Pula números ímpares
    }
    echo "  {$i}\n";
}
```

### `continue N`

```php
<?php

$matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

foreach ($matriz as $linha) {
    foreach ($linha as $valor) {
        if ($valor % 2 === 0) {
            continue 2; // Pula para a PRÓXIMA LINHA
        }
        echo "{$valor} "; // Só imprime ímpares
    }
    echo "\n";
}
// 1 -> próxima linha (2 é par)
// -> próxima linha (4 é par)
// 7 9 -> (8 é par)
```

### `break` e `continue` em `switch`

Em `switch`, `break` encerra o switch, `continue` age como `break`:

```php
<?php

// Dentro de um switch aninhado em um loop:
$valores = [1, 2, 3, 'fim'];

foreach ($valores as $valor) {
    if ($valor === 'fim') {
        break; // Sai do foreach
    }

    switch ($valor) {
        case 1:
            echo "um\n";
            break; // Sai do switch
        case 2:
            echo "dois\n";
            continue 2; // Sai do switch E continua o foreach (próxima iteração)
        case 3:
            echo "três — esta linha nunca será atingida\n";
            break;
    }

    echo "Após o switch (valor {$valor})\n";
}
/*
Saída:
um
Após o switch (valor 1)
dois
três — esta linha nunca será atingida
*/
```

---

## `goto`

PHP suporta `goto`, mas seu uso é desencorajado. Pode tornar o código impossível de manter.

```php
<?php

$i = 0;

inicio_loop:
    $i++;
    echo "{$i} ";

    if ($i < 5) {
        goto inicio_loop;
    }
// 1 2 3 4 5
```

### Caso de uso raro: sair de loops muito aninhados

```php
<?php

// goto pode ser útil como alternativa a break N em casos muito complexos
foreach ($dados as $grupo) {
    foreach ($grupo as $item) {
        foreach ($item as $subItem) {
            if (condicaoDeSaida($subItem)) {
                goto saida_limpa;
            }
            processarItem($subItem);
        }
    }
}

saida_limpa:
    echo "Processamento concluído ou interrompido.\n";
```

> ⚠️ **Cuidado**: O `goto` NÃO pode entrar em funções, classes, loops ou
> estruturas de controle. Só pode saltar dentro do mesmo escopo.

```php
<?php
// Isso NÃO funciona:
goto dentro;
for ($i = 0; $i < 5; $i++) {
    dentro:
    echo "{$i}\n";
}
// Erro: goto into loop
```

---

## `return`

Dentro de uma função, `return` encerra a execução e retorna um valor. Fora de função, retorna o controle do script (útil em includes condicionais).

```php
<?php

function dividir(float $a, float $b): float
{
    if ($b === 0.0) {
        throw new \DivisionByZeroError('Divisão por zero');
    }
    return $a / $b;
    // Código após return nunca é executado
    echo "Esta linha nunca será impressa";
}

$resultado = dividir(10, 2);
echo $resultado; // 5
```

### `return` em arquivos incluídos

```php
<?php
// config.php
return [
    'host'     => 'localhost',
    'database' => 'banco_dev',
    'username' => 'root',
    'password' => 'senha123',
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

Já vimos nos módulos anteriores. Deve ser a primeira instrução do arquivo:

```php
<?php

declare(strict_types=1);

function somar(int $a, int $b): int
{
    return $a + $b;
}

// somar(1, "2"); // TypeError com strict_types
```

### `declare(ticks=N)`

Define um "tick" — um evento que ocorre a cada `N` statements executados:

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
// Imprime "tick!" a cada statement
```

`ticks` quase nunca são usados. Ferramentas como profilers podem usá-los.

### `declare(encoding='UTF-8')`

Especifica a codificação do script (PHP 5.6+):

```php
<?php

declare(encoding='UTF-8');

// O script é interpretado como UTF-8
```

---

## Sintaxe alternativa para templates

PHP oferece uma sintaxe alternativa para estruturas de controle, ideal para templates onde se mescla HTML:

### `if:` / `endif;`

```php
<?php if ($usuarioLogado): ?>
    <div class="dashboard">
        <h2>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?></h2>
        <a href="/perfil">Meu Perfil</a>
    </div>
<?php else: ?>
    <div class="login-box">
        <h2>Entrar</h2>
        <form method="post" action="/login">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="senha" placeholder="Senha">
            <button type="submit">Entrar</button>
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
            <th>Produto</th>
            <th>Preço</th>
            <th>Estoque</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($produtos as $i => $produto): ?>
        <tr class="<?= $produto['estoque'] === 0 ? 'esgotado' : '' ?>">
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($produto['nome']) ?></td>
            <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
            <td><?= $produto['estoque'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
```

### `for:` / `endfor;`

```php
<select name="ano">
    <option value="">Selecione o ano</option>
    <?php for ($ano = date('Y'); $ano >= 2000; $ano--): ?>
        <option value="<?= $ano ?>"><?= $ano ?></option>
    <?php endfor; ?>
</select>
```

### `while:` / `endwhile;`

```php
<h2>Posts recentes</h2>
<?php while ($post = $posts->fetch()): ?>
    <article>
        <h3><?= htmlspecialchars($post['titulo']) ?></h3>
        <p><?= nl2br(htmlspecialchars($post['resumo'])) ?></p>
        <time><?= date('d/m/Y', strtotime($post['data_publicacao'])) ?></time>
    </article>
<?php endwhile; ?>
```

### `switch:` / `endswitch;`

```php
<?php switch ($tipoUsuario): ?>
    <?php case 'admin': ?>
        <nav class="admin-menu">
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/usuarios">Usuários</a>
            <a href="/admin/config">Configurações</a>
        </nav>
        <?php break; ?>

    <?php case 'editor': ?>
        <nav class="editor-menu">
            <a href="/editor/posts">Posts</a>
            <a href="/editor/midia">Mídia</a>
        </nav>
        <?php break; ?>

    <?php default: ?>
        <nav class="user-menu">
            <a href="/perfil">Perfil</a>
            <a href="/meus-posts">Meus Posts</a>
        </nav>
<?php endswitch; ?>
```

---

## Exemplo integrador

Um script que combina várias estruturas de controle:

```php
<?php

declare(strict_types=1);

function classificarNumeros(array $numeros): array
{
    $classificacao = [
        'pares'     => [],
        'impares'   => [],
        'primos'    => [],
        'negativos' => [],
        'zeros'     => 0,
    ];

    foreach ($numeros as $numero) {
        if ($numero === 0) {
            $classificacao['zeros']++;
            continue;
        }

        if ($numero < 0) {
            $classificacao['negativos'][] = $numero;
            $numero = abs($numero);
        }

        if ($numero % 2 === 0) {
            $classificacao['pares'][] = $numero;
        } else {
            $classificacao['impares'][] = $numero;
        }

        if (ehPrimo($numero)) {
            $classificacao['primos'][] = $numero;
        }
    }

    return $classificacao;
}

function ehPrimo(int $n): bool
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

// Teste
$numeros = [-7, -3, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 17, 0, 42];
$resultado = classificarNumeros($numeros);

foreach ($resultado as $categoria => $valores) {
    if ($categoria === 'zeros') {
        echo "Zeros encontrados: {$valores}\n";
    } else {
        echo ucfirst($categoria) . ": " . implode(', ', $valores) . "\n";
    }
}
/*
Pares: 2, 4, 6, 8, 10, 42
Impares: 7, 3, 3, 5, 7, 9, 11, 13, 17
Primos: 2, 7, 3, 3, 5, 7, 11, 13, 17
Negativos: -7, -3
Zeros encontrados: 2
*/
```

---

## 📚 Referências

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

[→ 06 — Funções (em breve)](./06-funcoes.md)
