# 08 — Strings em PHP

## Índice

1. [Aspas Simples vs Aspas Duplas](#aspas-simples-vs-aspas-duplas)
2. [Heredoc e Nowdoc](#heredoc-e-nowdoc)
3. [Manipulação Básica de Strings](#manipulação-básica-de-strings)
4. [Funções Multibyte (mb_*)](#funções-multibyte-mb_)
5. [Novas Funções Multibyte (PHP 8.4+)](#novas-funções-multibyte-php-84)
6. [Explode e Implode / Join](#explode-e-implode--join)
7. [Formatação com sprintf, printf, vsprintf](#formatação-com-sprintf-printf-vsprintf)
8. [Verificações Modernas (PHP 8.0+)](#verificações-modernas-php-80)
9. [Segurança: HTML e XSS](#segurança-html-e-xss)
10. [Utilidades: nl2br, wordwrap](#utilidades-nl2br-wordwrap)
11. [Expressões Regulares Básicas](#expressões-regulares-básicas)
12. [Divisão de Strings em Arrays](#divisão-de-strings-em-arrays)
13. [Comparação de Strings](#comparação-de-strings)
14. [Referências](#referências)

---

## Aspas Simples vs Aspas Duplas

### Aspas Simples

Conteúdo **não é interpretado**. Apenas duas sequências de escape são reconhecidas: `\\` (barra invertida) e `\'` (aspas simples escapada):

```php
<?php

$nome = 'PHP';

echo 'Olá, $nome!';           // Olá, $nome! (NÃO interpola)
echo 'Custo: R$ 99,90';       // Custo: R$ 99,90
echo 'Aspas \'simples\'';     // Aspas 'simples'
echo 'Barra \\ invertida';    // Barra \ invertida
```

### Aspas Duplas

Realizam **interpolação de variáveis** e reconhecem sequências de escape como `\n`, `\r`, `\t`, `\\`, `\"`, `\$`:

```php
<?php

$linguagem = 'PHP';
$versao = 8.5;

echo "Estudando $linguagem $versao";    // Estudando PHP 8.5

// Sequências de escape
echo "Linha 1\nLinha 2\nLinha 3";
// Linha 1
// Linha 2
// Linha 3
```

### Interpolação Complexa com Chaves `{}`

Para expressões mais complexas dentro de strings com aspas duplas, use `{}`:

```php
<?php

$frutas = ['maçã', 'banana', 'laranja'];
echo "Primeira fruta: {$frutas[0]}";          // Primeira fruta: maçã

class Usuario {
    public function getNome(): string {
        return 'Maria';
    }
}
$u = new Usuario();
echo "Nome: {$u->getNome()}";                 // Nome: Maria

// Acessar propriedades de array associativo
$config = ['host' => 'localhost', 'porta' => 3306];
echo "Conectando em {$config['host']}:{$config['porta']}";
// Conectando em localhost:3306

$valor = 42;
echo "O dobro de {$valor} é " . ($valor * 2); // O dobro de 42 é 84
```

### Performance

Aspas simples são mais rápidas que aspas duplas, já que o PHP não precisa escanear por variáveis. Essa diferença é irrelevante na maioria dos casos — escolha baseado na necessidade de interpolação.

---

## Heredoc e Nowdoc

### Heredoc

Equivalente a aspas duplas (interpola variáveis), mas permite múltiplas linhas sem concatenar:

```php
<?php

$nome = 'Carlos';
$idade = 28;

$html = <<<HTML
<div class="usuario">
    <h2>Nome: {$nome}</h2>
    <p>Idade: {$idade} anos</p>
</div>
HTML;

echo $html;
```

Regras importantes:
- O identificador de abertura `<<<IDENTIFICADOR` deve ser seguido por uma nova linha
- O identificador de fechamento (`IDENTIFICADOR;`) deve estar **na primeira coluna** (sem indentação)
- A linha de fechamento deve conter **apenas** o identificador e `;`

### Heredoc com Indentação (PHP 7.3+)

A partir do PHP 7.3, o identificador de fechamento **pode** ser indentado. A indentação do fechador determina quantos espaços são removidos de cada linha:

```php
<?php

function gerarTemplate(): string
{
    $titulo = 'Bem-vindo';

    return <<<HTML
        <section>
            <h1>{$titulo}</h1>
            <p>PHP 8.5 é incrível.</p>
        </section>
        HTML; // indentação do fechador: 8 espaços
}
// Todas as linhas terão 8 espaços removidos da esquerda

echo gerarTemplate();
```

### Nowdoc

Equivalente a aspas simples — **não interpola** variáveis. O identificador de abertura deve estar entre aspas simples:

```php
<?php

$framework = 'Laravel';

$texto = <<<'TXT'
Neste bloco, $framework não será interpolado.
Todas as sequências como \n e \t são tratadas literalmente.
Aqui vai uma barra: \\ e um cifrão: \$var
TXT;

echo $texto;
// Neste bloco, $framework não será interpolado.
// Todas as sequências como \n e \t são tratadas literalmente.
// Aqui vai uma barra: \\ e um cifrão: \$var
```

Nowdoc com indentação funciona da mesma forma (PHP 7.3+):

```php
<?php

$config = <<<'JSON'
    {
        "host": "localhost",
        "port": 3306
    }
    JSON;

echo $config;
```

---

## Manipulação Básica de Strings

### `strlen()` — Comprimento da String

```php
<?php

echo strlen('PHP 8.5');   // 7
echo strlen('café');      // 5  — CUIDADO: conta bytes, não caracteres!
```

### `strpos()` — Posição da Primeira Ocorrência

```php
<?php

$frase = 'O rato roeu a roupa do rei de Roma';

$pos = strpos($frase, 'rato');
echo $pos; // 2 (posição baseada em zero)

$pos2 = strpos($frase, 'Roma');
echo $pos2; // 30

// Não encontrado retorna false
$inexistente = strpos($frase, 'Python');
var_dump($inexistente); // bool(false)
```

⚠️ **Cuidado:** `strpos()` pode retornar índice `0`, que é falsy. Sempre compare com `=== false`:

```php
<?php

$str = 'PHP é incrível';

if (strpos($str, 'PHP') !== false) {
    echo 'Encontrado!';
}
```

### `stripos()` — Busca Case-Insensitive

```php
<?php

echo stripos('Olá MUNDO', 'mundo'); // 4
```

### `str_replace()` — Substituir Todas as Ocorrências

```php
<?php

$texto = 'O gato preto pulou sobre o gato branco';
$novo = str_replace('gato', 'cachorro', $texto);
echo $novo; // O cachorro preto pulou sobre o cachorro branco

// Substituições múltiplas com arrays:
$buscas = ['gato', 'preto', 'branco'];
$trocas = ['pássaro', 'azul', 'amarelo'];
echo str_replace($buscas, $trocas, $texto);
// O pássaro azul pulou sobre o pássaro amarelo
```

### `str_ireplace()` — Substituição Case-Insensitive

```php
<?php

echo str_ireplace('PHP', 'JavaScript', 'php é legal e PHP também');
// JavaScript é legal e JavaScript também
```

### `substr()` — Extrair Substring

```php
<?php

$texto = 'PHP 8.5 - Novidades';

echo substr($texto, 0, 3);   // PHP (a partir do 0, 3 caracteres)
echo substr($texto, 4, 3);   // 8.5
echo substr($texto, -9);     // Novidades (últimos 9 caracteres)
echo substr($texto, 4, -3);  // 8.5 - Novid (remove 3 do final)
```

### `trim()`, `ltrim()`, `rtrim()` — Remover Espaços

```php
<?php

$sujo = "   \t  Hello, World!  \n  ";

echo trim($sujo);   // "Hello, World!"
echo ltrim($sujo);  // "Hello, World!  \n  "
echo rtrim($sujo);  // "   \t  Hello, World!"

// Remover caracteres específicos:
$valor = '...R$ 99,90...';
echo trim($valor, '.');            // "R$ 99,90"
echo trim($valor, '.R$ ');         // "99,90"
```

### `strtoupper()`, `strtolower()`, `ucfirst()`, `ucwords()`, `lcfirst()`

```php
<?php

$nome = 'joão silva';

echo strtoupper($nome);    // JOÃO SILVA
echo strtolower($nome);    // joão silva
echo ucfirst($nome);       // João silva
echo ucwords($nome);       // João Silva
echo lcfirst('PHP');       // pHP

// IMPORTANTE: estas funções NÃO lidam com UTF-8 corretamente.
// Para caracteres acentuados, use mb_* equivalentes (veja abaixo).
echo ucfirst('árvore');    // árvore (não converte 'á' para 'Á')
```

---

## Funções Multibyte (mb_*)

As funções `mb_*` manipulam strings **multibyte** como UTF-8. Para uso com português, **sempre** prefira as versões `mb_*`:

### `mb_strlen()`

```php
<?php

echo strlen('café');         // 5 (bytes: c-a-f-é = 4+1? Não, depende do encoding)
echo mb_strlen('café');      // 4 (caracteres)
echo mb_strlen('ação');      // 4
echo mb_strlen('日本語');     // 3
```

### `mb_substr()`

```php
<?php

$texto = 'Programação em PHP';

echo mb_substr($texto, 0, 11);   // Programação
echo mb_substr($texto, -3);       // PHP
echo mb_substr($texto, 12);       // em PHP
```

### `mb_strpos()`

```php
<?php

$frase = 'A explicação é simples';

echo mb_strpos($frase, 'ção');     // 9 (posição correta em caracteres)
echo mb_strpos($frase, 'é');       // 14
```

### Outras Funções `mb_*` Importantes

| Função | Equivalente não-multibyte |
|--------|---------------------------|
| `mb_strtolower()` | `strtolower()` |
| `mb_strtoupper()` | `strtoupper()` |
| `mb_convert_case()` | `ucfirst()` / `ucwords()` |
| `mb_strripos()` | `strripos()` |
| `mb_substr_count()` | `substr_count()` |
| `mb_str_split()` | `str_split()` |
| `mb_convert_encoding()` | — |

```php
<?php

// Converter case corretamente com UTF-8
echo mb_strtoupper('café');              // CAFÉ
echo mb_strtolower('AÇÃO');              // ação

// mb_convert_case com modos:
// MB_CASE_UPPER, MB_CASE_LOWER, MB_CASE_TITLE, MB_CASE_FOLD
echo mb_convert_case('joão da silva', MB_CASE_TITLE, 'UTF-8');
// João Da Silva
```

### Configuração de Encoding Padrão

Defina no início do script (ou no `php.ini`):

```php
<?php

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

// Ou via php.ini / .user.ini:
// mbstring.internal_encoding = UTF-8
```

---

## Novas Funções Multibyte (PHP 8.4+)

### `mb_trim()`, `mb_ltrim()`, `mb_rtrim()` — PHP 8.4+

**PHP 8.4+** — Versões multibyte das funções `trim`, `ltrim` e `rtrim`, seguras para UTF-8:

```php
<?php

$texto = "　こんにちは　";  // espaços ideográficos (U+3000)

// trim() NÃO remove caracteres multibyte
echo trim($texto);              // "　こんにちは　" (não alterado)

// mb_trim() remove corretamente
echo mb_trim($texto);           // "こんにちは"

// Com caracteres específicos:
$valor = '...R$ 99,90...';
echo mb_trim($valor, '.');      // "R$ 99,90"

// ltrim e rtrim
echo mb_ltrim('   espaços à esquerda', ' ');    // "espaços à esquerda"
echo mb_rtrim('espaços à direita   ', ' ');    // "espaços à direita"
```

### `mb_ucfirst()` e `mb_lcfirst()` — PHP 8.4+

**PHP 8.4+** — Versões multibyte de `ucfirst` e `lcfirst`:

```php
<?php

// PHP < 8.4: ucfirst não lidava com acentos
echo ucfirst('árvore');              // árvore (nenhuma mudança!)

// PHP 8.4+: mb_ucfirst converte corretamente
echo mb_ucfirst('árvore');           // Árvore
echo mb_ucfirst('último dia');       // Último dia

// mb_lcfirst
echo mb_lcfirst('OLA');              // oLA
echo mb_lcfirst('ÁRVORE');           // áRVORE

// Aplicação: capitalizar nome de cidade
$cidade = 'são paulo';
echo mb_ucfirst($cidade);            // São paulo
echo mb_convert_case($cidade, MB_CASE_TITLE, 'UTF-8'); // São Paulo
```

💡 **Dica:** Use `mb_ucfirst` para capitalizar a primeira letra de nomes próprios, títulos e início de frases em português, que com frequência começam com acentos.

---

## Explode e Implode / Join

### `explode()` — Dividir String em Array

```php
<?php

$csv = 'João,Maria,Pedro,Ana,Bia';
$nomes = explode(',', $csv);
print_r($nomes); // ['João', 'Maria', 'Pedro', 'Ana', 'Bia']

// Com limite (terceiro parâmetro):
$frase = 'um dois três quatro cinco';
print_r(explode(' ', $frase, 3));
// ['um', 'dois', 'três quatro cinco']

// Limite negativo — remove os últimos N elementos:
print_r(explode(' ', $frase, -2));
// ['um', 'dois', 'três']
```

### `implode()` / `join()` — Juntar Array em String

`implode()` e `join()` são **idênticos** (alias):

```php
<?php

$partes = ['2026', '08', '04'];
echo implode('-', $partes);  // 2026-08-04
echo join('/', $partes);     // 2026/08/04

// Implode sem delimitador:
echo implode($partes);      // 20260804
```

### Pipeline Típico: `explode` → processar → `implode`

```php
<?php

$nomes = 'ana, carlos, BIA, PEDRO, joão';

// Normalizar: trim e capitalizar cada nome
$partes = explode(',', $nomes);
$partes = array_map(fn(string $n): string => mb_ucfirst(mb_strtolower(trim($n))), $partes);
$normalizado = implode(', ', $partes);

echo $normalizado; // Ana, Carlos, Bia, Pedro, João
```

---

## Formatação com sprintf, printf, vsprintf

### `sprintf()` — Formatar e Retornar String

```php
<?php

$produto = 'Notebook';
$preco   = 3500.00;
$qtd     = 2;

echo sprintf('Produto: %s — Preço unitário: R$ %.2f — Quantidade: %d', $produto, $preco, $qtd);
// Produto: Notebook — Preço unitário: R$ 3500.00 — Quantidade: 2
```

### Principais Placeholders

| Placeholder | Tipo | Exemplo |
|-------------|------|---------|
| `%s` | String | `sprintf('%s', 'PHP')` |
| `%d` | Inteiro (decimal) | `sprintf('%d', 42)` |
| `%f` | Float | `sprintf('%.2f', 99.9)` → `99.90` |
| `%b` | Binário | `sprintf('%b', 10)` → `1010` |
| `%x` / `%X` | Hexadecimal | `sprintf('%x', 255)` → `ff` |
| `%o` | Octal | `sprintf('%o', 8)` → `10` |
| `%%` | Literal `%` | `sprintf('100%%')` → `100%` |

### Formatos Numéricos com `sprintf`

```php
<?php

$valor = 1234.5678;

echo sprintf('R$ %.2f', $valor);        // R$ 1234.57
echo sprintf('%\'.2f', $valor);          // 1234.57 (aspas simples é o padding char)

// Preenchimento com zeros à esquerda:
echo sprintf('%08d', 42);                // 00000042

// Alinhamento com largura fixa:
echo sprintf("[%10s]", 'PHP');           // [       PHP]
echo sprintf("[%-10s]", 'PHP');          // [PHP       ]
echo sprintf("[%'.-10s]", 'PHP');        // [PHP.......]
```

### `printf()` — Formatar e Imprimir na Tela

```php
<?php

printf('Data: %s, Hora: %s', date('d/m/Y'), date('H:i'));
// Data: 04/08/2026, Hora: 10:30
```

### `vsprintf()` — Formatar com Array de Argumentos

```php
<?php

function logFormatado(string $template, mixed ...$args): string
{
    $timestamp = date('Y-m-d H:i:s');
    $mensagem = vsprintf($template, $args);
    return "[{$timestamp}] {$mensagem}";
}

echo logFormatado('Usuário %s fez login de %s', 'admin', '192.168.1.1');
// [2026-08-04 10:30:00] Usuário admin fez login de 192.168.1.1
```

---

## Verificações Modernas (PHP 8.0+)

### `str_contains()` — PHP 8.0+

Verifica se uma string **contém** outra:

```php
<?php

$url = 'https://api.exemplo.com/v1/usuarios';

var_dump(str_contains($url, 'https'));         // bool(true)
var_dump(str_contains($url, 'ftp'));           // bool(false)

// Muito mais legível que strpos:
// Antes:  strpos($url, 'https') !== false
// Agora:  str_contains($url, 'https')
```

### `str_starts_with()` — PHP 8.0+

Verifica se uma string **começa com** outra:

```php
<?php

$email = 'usuario@dominio.com';

var_dump(str_starts_with($email, 'admin'));    // bool(false)
var_dump(str_starts_with($email, 'usuario'));   // bool(true)

// Validação simples de URL:
$url = 'https://exemplo.com';
if (str_starts_with($url, 'https://')) {
    echo 'Conexão segura';
}
```

### `str_ends_with()` — PHP 8.0+

Verifica se uma string **termina com** outra:

```php
<?php

$arquivo = 'relatorio_financeiro.pdf';

var_dump(str_ends_with($arquivo, '.pdf'));     // bool(true)
var_dump(str_ends_with($arquivo, '.docx'));    // bool(false)

// Filtrar extensões permitidas:
$permitidas = ['.jpg', '.png', '.gif', '.webp'];
$upload = 'foto_perfil.png';

$valido = array_any($permitidas, fn(string $ext): bool => str_ends_with($upload, $ext));
var_dump($valido); // bool(true)
```

### Exemplo Combinado

```php
<?php

function validarEmail(string $email): bool
{
    return str_contains($email, '@')
        && !str_starts_with($email, '@')
        && !str_ends_with($email, '@')
        && str_contains($email, '.');
}

var_dump(validarEmail('user@dominio.com'));  // bool(true)
var_dump(validarEmail('@dominio.com'));      // bool(false)
var_dump(validarEmail('user@'));             // bool(false)
```

---

## Segurança: HTML e XSS

### `htmlspecialchars()` — Escapar Caracteres Especiais

Converte caracteres especiais HTML em entidades, prevenindo **XSS**:

```php
<?php

$input = '<script>alert("XSS")</script>';

// Sem escape (PERIGOSO!):
echo $input;
// <script>alert("XSS")</script> — seria executado no navegador

// Com escape (SEGURO):
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
// &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt; — exibido como texto
```

### Flags Importantes

| Flag | Descrição |
|------|-----------|
| `ENT_COMPAT` | Converte `"` mas não `'` (padrão) |
| `ENT_QUOTES` | Converte tanto `"` quanto `'` |
| `ENT_NOQUOTES` | Não converte aspas |
| `ENT_HTML5` | Usa entidades HTML5 |

```php
<?php

$dados = "O'Brian disse: \"Olá\"";

echo htmlspecialchars($dados, ENT_COMPAT, 'UTF-8');
// O'Brian disse: &quot;Olá&quot;

echo htmlspecialchars($dados, ENT_QUOTES, 'UTF-8');
// O&#039;Brian disse: &quot;Olá&quot;

// Sempre use ENT_QUOTES para máxima segurança
```

### `htmlspecialchars()` vs `htmlentities()`

- **`htmlspecialchars()`**: Converte apenas `&`, `"`, `'`, `<`, `>` — o essencial para segurança
- **`htmlentities()`**: Converte **todos** os caracteres que possuem entidade HTML equivalente

```php
<?php

$texto = 'Ação e Coração';

echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
// Ação e Coração (caracteres acentuados preservados)

echo htmlentities($texto, ENT_QUOTES, 'UTF-8');
// A&ccedil;&atilde;o e Cora&ccedil;&atilde;o (entidades HTML)
```

💡 **Dica:** Para segurança contra XSS em saída HTML, `htmlspecialchars()` é suficiente e mais previsível que `htmlentities()`. Reserve `htmlentities()` para casos onde você precisa garantir ASCII puro (ex: emails antigos).

### `strip_tags()` — Remover Tags HTML

```php
<?php

$html = '<p>Este é um <strong>texto</strong> com <a href="#">link</a>.</p>';

// Remove todas as tags
echo strip_tags($html);
// Este é um texto com link.

// Permite tags específicas
echo strip_tags($html, '<strong><em>');
// Este é um <strong>texto</strong> com link.
```

⚠️ **Cuidado:** `strip_tags()` **não** substitui `htmlspecialchars()`. Ele remove tags mas não escapa conteúdo. Para contexto HTML, sempre use `htmlspecialchars()`.

### Função Auxiliar para Templates

```php
<?php

/**
 * Escapa valor para saída segura em HTML.
 * Se o valor for null, retorna string vazia.
 */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Em templates:
$usuario = ['nome' => '<b>João</b>', 'bio' => 'Dev & "hacker" ético'];
echo '<h1>' . e($usuario['nome']) . '</h1>';
echo '<p>' . e($usuario['bio']) . '</p>';
// <h1>&lt;b&gt;João&lt;/b&gt;</h1>
// <p>Dev &amp; &quot;hacker&quot; ético</p>
```

---

## Utilidades: nl2br, wordwrap

### `nl2br()` — Converter Quebras de Linha em `<br>`

```php
<?php

$comentario = "Primeira linha.\nSegunda linha.\nTerceira linha.";

echo nl2br($comentario);
// Primeira linha.<br />
// Segunda linha.<br />
// Terceira linha.
```

Para usar `<br>` (XHTML) em vez de `<br />`:

```php
<?php

echo nl2br($comentario, false); // <br> em vez de <br />
```

### `wordwrap()` — Quebrar Linhas por Comprimento

```php
<?php

$texto = 'O rápido cachorro marrom pula sobre o gato preguiçoso.';

echo wordwrap($texto, 20, "<br />\n");
/*
O rápido cachorro
marrom pula sobre o
gato preguiçoso.
*/

// Com corte forçado (quarto parâmetro true):
echo wordwrap('URLMuitooooooooooooooooLonga', 15, "<br />\n", true);
// URLMuitoooooooo
// ooooooooLonga
```

---

## Expressões Regulares Básicas

Expressões regulares (regex) são padrões de busca em strings. Em PHP, as funções PCRE (`preg_*`) são o padrão moderno.

### `preg_match()` — Buscar Padrão

```php
<?php

$email = 'usuario@dominio.com.br';

// Verificar formato de email (simplificado)
$padrao = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (preg_match($padrao, $email)) {
    echo 'Email válido';
} else {
    echo 'Email inválido';
}
```

### Capturar Grupos

```php
<?php

$data = 'Data: 2026-08-04';

if (preg_match('/Data: (\d{4})-(\d{2})-(\d{2})/', $data, $matches)) {
    print_r($matches);
    /*
    Array
    (
        [0] => Data: 2026-08-04
        [1] => 2026  // ano
        [2] => 08    // mês
        [3] => 04    // dia
    )
    */
    echo "Ano: {$matches[1]}, Mês: {$matches[2]}, Dia: {$matches[3]}";
    // Ano: 2026, Mês: 08, Dia: 04
}
```

### `preg_match_all()` — Todas as Ocorrências

```php
<?php

$html = '<a href="/home">Home</a> <a href="/about">Sobre</a> <a href="/contact">Contato</a>';

preg_match_all('/href="([^"]+)"/', $html, $matches);
print_r($matches[1]); // ['/home', '/about', '/contact']
```

### `preg_replace()` — Substituir com Regex

```php
<?php

// Remover tudo que não é dígito (ex: formatar telefone)
$telefone = '(11) 98765-4321';
$apenasNumeros = preg_replace('/\D/', '', $telefone);
echo $apenasNumeros; // 11987654321

// Mascarar parte de um email
$email = 'usuario.secreto@provedor.com.br';
$mascarado = preg_replace('/(.{3}).*(@.*)/', '$1***$2', $email);
echo $mascarado; // usu***@provedor.com.br

// Substituir múltiplos espaços por um único
$texto = 'PHP     é         incrível';
$limpo = preg_replace('/\s+/', ' ', $texto);
echo $limpo; // PHP é incrível
```

### `preg_split()` — Dividir com Regex

```php
<?php

$csv = 'João, Maria; Pedro| Ana, Bia';
$nomes = preg_split('/[,;|]\s*/', $csv);
print_r($nomes); // ['João', 'Maria', 'Pedro', 'Ana', 'Bia']
```

### Delimitadores e Modificadores Comuns

| Componente | Exemplo | Significado |
|------------|---------|-------------|
| Delimitador `/` | `/padrao/` | Inicia e termina a regex |
| `i` | `/padrao/i` | Case-insensitive |
| `u` | `/padrao/u` | Modo UTF-8 |
| `m` | `/padrao/m` | Multiline (^ e $ em cada linha) |
| `s` | `/padrao/s` | `.` inclui quebras de linha |
| `\d` | — | Dígito [0-9] |
| `\D` | — | Não-dígito |
| `\w` | — | Palavra [a-zA-Z0-9_] |
| `\W` | — | Não-palavra |
| `\s` | — | Espaço em branco |
| `\S` | — | Não-espaço |
| `+` | — | 1 ou mais |
| `*` | — | 0 ou mais |
| `?` | — | 0 ou 1 |
| `{n,m}` | — | n a m vezes |

---

## Divisão de Strings em Arrays

### `str_split()` — Dividir em Pedaços de N Caracteres

```php
<?php

$hex = 'FF00AA';
print_r(str_split($hex, 2)); // ['FF', '00', 'AA']

// Sem comprimento, divide em caracteres:
print_r(str_split('PHP')); // ['P', 'H', 'P']
```

⚠️ **Cuidado:** `str_split()` trabalha com **bytes**, não com caracteres. Para UTF-8, use `mb_str_split()`.

### `grapheme_str_split()` — PHP 8.4+

**PHP 8.4+** — Divide considerando **grapheme clusters** (caracteres visuais compostos, como emojis e caracteres acentuados):

```php
<?php

// str_split falha com caracteres multibyte:
$str_split_result = str_split('café');
print_r($str_split_result); // ['c', 'a', 'f', '�', '�'] — corrompido!

// mb_str_split funciona para multibyte básico:
print_r(mb_str_split('café')); // ['c', 'a', 'f', 'é'] — OK

// grapheme_str_split lida com tudo, incluindo emojis compostos:
$bandeira = '🇧🇷';  // bandeira do Brasil (2 code points combinados)
echo grapheme_strlen($bandeira) . PHP_EOL;   // 1 (um grapheme cluster)
print_r(grapheme_str_split($bandeira));      // ['🇧🇷']

// Família com emoji:
$familia = '👨‍👩‍👧';  // família (4 emojis combinados com ZWJ)
echo grapheme_strlen($familia) . PHP_EOL;    // 1
print_r(grapheme_str_split($familia));       // ['👨‍👩‍👧']
```

💡 **Dica:** Use `grapheme_str_split()` sempre que manipular strings com emojis, caracteres combinados ou texto internacional. É a função mais segura para divisão caractere-a-caractere.

---

## Comparação de Strings

### `==` vs `===`

- `==` compara com **coerção de tipos**
- `===` compara sem coerção (valor e tipo)

```php
<?php

var_dump('123' == 123);    // bool(true)  — coerção: string '123' vira int 123
var_dump('123' === 123);   // bool(false) — tipos diferentes

var_dump(0 == '');         // bool(true)  — CUIDADO! '' é convertido para 0
var_dump(0 === '');        // bool(false)
var_dump(0 == 'zero');     // bool(true)  — 'zero' não é numérico, vira 0
var_dump(0 === 'zero');    // bool(false)
```

⚠️ **Cuidado:** Sempre use `===` para comparar com `false`, `0`, `''` ou `null`, pois `==` pode dar resultados inesperados.

### `strcmp()` — Comparação Binária

```php
<?php

var_dump(strcmp('abc', 'abc'));  // int(0)  — iguais
var_dump(strcmp('abc', 'abd'));  // int(-1) — 'abc' < 'abd'
var_dump(strcmp('abd', 'abc'));  // int(1)  — 'abd' > 'abc'

// Case-sensitive
var_dump(strcmp('ABC', 'abc'));  // int(-1)
```

### `strcasecmp()` — Comparação Case-Insensitive

```php
<?php

var_dump(strcasecmp('ABC', 'abc')); // int(0) — iguais, ignorando case
```

### `strnatcmp()` — Comparação com Ordem Natural

Ordena strings que contêm números de forma intuitiva (1, 2, 3, ..., 10, 11, em vez de 1, 10, 11, 2, 3...):

```php
<?php

$arquivos = ['img10.jpg', 'img2.jpg', 'img1.jpg', 'img20.jpg'];

usort($arquivos, 'strnatcmp');
print_r($arquivos); // ['img1.jpg', 'img2.jpg', 'img10.jpg', 'img20.jpg']
```

---

## 📚 Referências

- [Documentação oficial: Strings](https://www.php.net/manual/pt_BR/language.types.string.php)
- [Funções de String](https://www.php.net/manual/pt_BR/ref.strings.php)
- [Heredoc / Nowdoc](https://www.php.net/manual/pt_BR/language.types.string.php#language.types.string.syntax.heredoc)
- [str_contains() — PHP 8.0](https://www.php.net/manual/pt_BR/function.str-contains.php)
- [str_starts_with() — PHP 8.0](https://www.php.net/manual/pt_BR/function.str-starts-with.php)
- [str_ends_with() — PHP 8.0](https://www.php.net/manual/pt_BR/function.str-ends-with.php)
- [htmlspecialchars()](https://www.php.net/manual/pt_BR/function.htmlspecialchars.php)
- [Funções Multibyte (mbstring)](https://www.php.net/manual/pt_BR/book.mbstring.php)
- [mb_trim() — PHP 8.4](https://www.php.net/manual/pt_BR/function.mb-trim.php)
- [mb_ucfirst() — PHP 8.4](https://www.php.net/manual/pt_BR/function.mb-ucfirst.php)
- [grapheme_str_split() — PHP 8.4](https://www.php.net/manual/pt_BR/function.grapheme-str-split.php)
- [Expressões Regulares PCRE](https://www.php.net/manual/pt_BR/book.pcre.php)
- [sprintf()](https://www.php.net/manual/pt_BR/function.sprintf.php)
- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

---

> **Capítulo anterior:** [07 — Arrays](07-arrays.md)
> **Próximo capítulo:** [09 — Programação Orientada a Objetos (Parte 1)](09-oop.md)
