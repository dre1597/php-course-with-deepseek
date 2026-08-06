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

Conteúdo **não é interpretado**. Só `\\` e `\'` são reconhecidos como escape:

```php
<?php

$name = 'PHP';

echo 'Hello, $name!';           // Hello, $name! (does NOT interpolate)
echo 'Cost: $ 99.90';           // Cost: $ 99.90
echo 'Escaped \'quote\'';       // Escaped 'quote'
echo 'Backslash \\ character';  // Backslash \ character
```

### Aspas Duplas

Realizam **interpolação de variáveis** e reconhecem sequências de escape como `\n`, `\r`, `\t`, `\\`, `\"`, `\$`:

```php
<?php

$language = 'PHP';
$version = 8.5;

echo "Learning $language $version";    // Learning PHP 8.5

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

$fruits = ['apple', 'banana', 'orange'];
echo "First fruit: {$fruits[0]}";          // First fruit: apple

class User {
    public function getName(): string {
        return 'Mary';
    }
}
$user = new User();
echo "Name: {$user->getName()}";                 // Name: Mary

// Access associative array properties
$config = ['host' => 'localhost', 'port' => 3306];
echo "Connecting to {$config['host']}:{$config['port']}";
// Connecting to localhost:3306

$value = 42;
echo "Double of {$value} is " . ($value * 2); // Double of 42 is 84
```

### Performance

Aspas simples são mais rápidas — o PHP não escaneia por variáveis. A diferença é irrelevante na prática. Escolha pela necessidade de interpolação.

---

## Heredoc e Nowdoc

### Heredoc

Equivalente a aspas duplas (interpola variáveis), mas permite múltiplas linhas sem concatenar:

```php
<?php

$name = 'Charles';
$age = 28;

$html = <<<HTML
<div class="user">
    <h2>Name: {$name}</h2>
    <p>Age: {$age} years</p>
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

function generateTemplate(): string
{
    $title = 'Welcome';

    return <<<HTML
        <section>
            <h1>{$title}</h1>
            <p>PHP 8.5 is awesome.</p>
        </section>
        HTML; // closing indent: 8 spaces
}
// All lines will have 8 spaces stripped from the left

echo generateTemplate();
```

### Nowdoc

Equivalente a aspas simples — **não interpola** variáveis. O identificador de abertura deve estar entre aspas simples:

```php
<?php

$framework = 'Laravel';

$text = <<<'TXT'
In this block, $framework will not be interpolated.
All sequences like \n and \t are treated literally.
Here's a backslash: \\ and a dollar sign: \$var
TXT;

echo $text;
// In this block, $framework will not be interpolated.
// All sequences like \n and \t are treated literally.
// Here's a backslash: \\ and a dollar sign: \$var
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
echo strlen('café');      // 5  — WARNING: counts bytes, not characters!
```

### `strpos()` — Posição da Primeira Ocorrência

```php
<?php

$phrase = 'The quick brown fox jumps over the lazy dog';

$pos = strpos($phrase, 'fox');
echo $pos; // 16 (zero-based position)

$pos2 = strpos($phrase, 'dog');
echo $pos2; // 40

// Not found returns false
$notFound = strpos($phrase, 'Python');
var_dump($notFound); // bool(false)
```

**Cuidado:** `strpos()` pode retornar índice `0`, que é falsy. Sempre compare com `=== false`:

```php
<?php

$str = 'PHP is cool';

if (strpos($str, 'PHP') !== false) {
    echo 'Found!';
}
```

### `stripos()` — Busca Case-Insensitive

```php
<?php

echo stripos('Hello WORLD', 'world'); // 6
```

### `str_replace()` — Substituir Todas as Ocorrências

```php
<?php

$text = 'The black cat jumped over the white cat';
$new = str_replace('cat', 'dog', $text);
echo $new; // The black dog jumped over the white dog

// Multiple replacements with arrays:
$searches = ['cat', 'black', 'white'];
$replacements = ['bird', 'blue', 'yellow'];
echo str_replace($searches, $replacements, $text);
// The blue bird jumped over the yellow bird
```

### `str_ireplace()` — Substituição Case-Insensitive

```php
<?php

echo str_ireplace('PHP', 'JavaScript', 'php is cool and PHP too');
// JavaScript is cool and JavaScript too
```

### `substr()` — Extrair Substring

```php
<?php

$text = 'PHP 8.5 - New Features';

echo substr($text, 0, 3);   // PHP (from 0, 3 characters)
echo substr($text, 4, 3);   // 8.5
echo substr($text, -12);     // New Features (last 12 characters)
echo substr($text, 4, -5);  // 8.5 - New Fea (remove 5 from end)
```

### `trim()`, `ltrim()`, `rtrim()` — Remover Espaços

```php
<?php

$dirty = "   \t  Hello, World!  \n  ";

echo trim($dirty);   // "Hello, World!"
echo ltrim($dirty);  // "Hello, World!  \n  "
echo rtrim($dirty);  // "   \t  Hello, World!"

// Remover caracteres específicos:
$value = '...R$ 99,90...';
echo trim($value, '.');            // "R$ 99,90"
echo trim($value, '.R$ ');         // "99,90"
```

### `strtoupper()`, `strtolower()`, `ucfirst()`, `ucwords()`, `lcfirst()`

```php
<?php

$name = 'john smith';

echo strtoupper($name);    // JOHN SMITH
echo strtolower($name);    // john smith
echo ucfirst($name);       // John smith
echo ucwords($name);       // John Smith
echo lcfirst('PHP');       // pHP

// IMPORTANT: these functions do NOT handle UTF-8 correctly.
// For accented characters, use mb_* equivalents (see below).
echo ucfirst('árvore');    // árvore (does not convert 'á' to 'Á')
```

---

## Funções Multibyte (mb_*)

Funções `mb_*` manipulam strings **multibyte** (UTF-8). Para português, **sempre** prefira as versões `mb_*`:

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

$text = 'Programming in PHP';

echo mb_substr($text, 0, 11);   // Programming
echo mb_substr($text, -3);       // PHP
echo mb_substr($text, 15);       // in PHP
```

### `mb_strpos()`

```php
<?php

$phrase = 'The explanation is simple';

echo mb_strpos($phrase, 'tion');     // 9 (correct position in characters)
echo mb_strpos($phrase, 'is');       // 14
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

// Convert case correctly with UTF-8
echo mb_strtoupper('café');              // CAFÉ
echo mb_strtolower('AÇÃO');              // ação

// mb_convert_case with modes:
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

$text = "　こんにちは　";  // espaços ideográficos (U+3000)

// trim() NÃO remove caracteres multibyte
echo trim($text);              // "　こんにちは　" (não alterado)

// mb_trim() remove corretamente
echo mb_trim($text);           // "こんにちは"

// With specific characters:
$value = '...$ 99.90...';
echo mb_trim($value, '.');      // "$ 99.90"

// ltrim and rtrim
echo mb_ltrim('   left spaces', ' ');    // "left spaces"
echo mb_rtrim('right spaces   ', ' ');    // "right spaces"
```

### `mb_ucfirst()` e `mb_lcfirst()` — PHP 8.4+

**PHP 8.4+** — Versões multibyte de `ucfirst` e `lcfirst`:

```php
<?php

// PHP < 8.4: ucfirst não lidava com acentos
echo ucfirst('árvore');              // árvore (nenhuma mudança!)

// PHP 8.4+: mb_ucfirst converts correctly
echo mb_ucfirst('árvore');           // Árvore
echo mb_ucfirst('último dia');       // Último dia

// mb_lcfirst
echo mb_lcfirst('HELLO');            // hELLO
echo mb_lcfirst('ÁRVORE');           // áRVORE

// Application: capitalize city name
$city = 'são paulo';
echo mb_ucfirst($city);            // São paulo
echo mb_convert_case($city, MB_CASE_TITLE, 'UTF-8'); // São Paulo
```

**Dica:** `mb_ucfirst` resolve capitalização de palavras com acento, comum em português ('árvore' → 'Árvore').

---

## Explode e Implode / Join

### `explode()` — Dividir String em Array

```php
<?php

$csv = 'John,Mary,Peter,Anna,Bea';
$names = explode(',', $csv);
print_r($names); // ['John', 'Mary', 'Peter', 'Anna', 'Bea']

// With limit (third parameter):
$phrase = 'one two three four five';
print_r(explode(' ', $phrase, 3));
// ['one', 'two', 'three four five']

// Negative limit — removes the last N elements:
print_r(explode(' ', $phrase, -2));
// ['one', 'two', 'three']
```

### `implode()` / `join()` — Juntar Array em String

`implode()` e `join()` são **idênticos** (alias):

```php
<?php

$parts = ['2026', '08', '04'];
echo implode('-', $parts);  // 2026-08-04
echo join('/', $parts);     // 2026/08/04

// Implode sem delimitador:
echo implode($parts);      // 20260804
```

### Pipeline Típico: `explode` → processar → `implode`

```php
<?php

$names = 'anna, charles, BEA, PETER, john';

// Normalize: trim and capitalize each name
$parts = explode(',', $names);
$parts = array_map(fn(string $name): string => mb_ucfirst(mb_strtolower(trim($name))), $parts);
$normalized = implode(', ', $parts);

echo $normalized; // Anna, Charles, Bea, Peter, John
```

---

## Formatação com sprintf, printf, vsprintf

### `sprintf()` — Formatar e Retornar String

```php
<?php

$product = 'Notebook';
$price   = 3500.00;
$qty     = 2;

echo sprintf('Product: %s — Unit price: $ %.2f — Quantity: %d', $product, $price, $qty);
// Product: Notebook — Unit price: $ 3500.00 — Quantity: 2
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

$value = 1234.5678;

echo sprintf('$ %.2f', $value);        // $ 1234.57
echo sprintf('%\'.2f', $value);          // 1234.57 (single quote is the padding char)

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

printf('Date: %s, Time: %s', date('Y-m-d'), date('H:i'));
// Date: 2026-08-04, Time: 10:30
```

### `vsprintf()` — Formatar com Array de Argumentos

```php
<?php

function formatLog(string $template, mixed ...$args): string
{
    $timestamp = date('Y-m-d H:i:s');
    $message = vsprintf($template, $args);
    return "[{$timestamp}] {$message}";
}

echo formatLog('User %s logged in from %s', 'admin', '192.168.1.1');
// [2026-08-04 10:30:00] User admin logged in from 192.168.1.1
```

---

## Verificações Modernas (PHP 8.0+)

### `str_contains()` — PHP 8.0+

Verifica se uma string **contém** outra:

```php
<?php

$url = 'https://api.example.com/v1/users';

var_dump(str_contains($url, 'https'));         // bool(true)
var_dump(str_contains($url, 'ftp'));           // bool(false)

// Much more readable than strpos:
// Before:  strpos($url, 'https') !== false
// Now:  str_contains($url, 'https')
```

### `str_starts_with()` — PHP 8.0+

Verifica se uma string **começa com** outra:

```php
<?php

$email = 'user@domain.com';

var_dump(str_starts_with($email, 'admin'));    // bool(false)
var_dump(str_starts_with($email, 'user'));   // bool(true)

// Simple URL validation:
$url = 'https://example.com';
if (str_starts_with($url, 'https://')) {
    echo 'Secure connection';
}
```

### `str_ends_with()` — PHP 8.0+

Verifica se uma string **termina com** outra:

```php
<?php

$file = 'financial_report.pdf';

var_dump(str_ends_with($file, '.pdf'));     // bool(true)
var_dump(str_ends_with($file, '.docx'));    // bool(false)

// Filter allowed extensions:
$allowed = ['.jpg', '.png', '.gif', '.webp'];
$upload = 'profile_photo.png';

$valid = array_any($allowed, fn(string $ext): bool => str_ends_with($upload, $ext));
var_dump($valid); // bool(true)
```

### Exemplo Combinado

```php
<?php

function validateEmail(string $email): bool
{
    return str_contains($email, '@')
        && !str_starts_with($email, '@')
        && !str_ends_with($email, '@')
        && str_contains($email, '.');
}

var_dump(validateEmail('user@domain.com'));  // bool(true)
var_dump(validateEmail('@domain.com'));      // bool(false)
var_dump(validateEmail('user@'));             // bool(false)
```

---

## Segurança: HTML e XSS

### `htmlspecialchars()` — Escapar Caracteres Especiais

Converte caracteres especiais HTML em entidades, prevenindo **XSS**:

```php
<?php

$input = '<script>alert("XSS")</script>';

// Without escape (DANGEROUS!):
echo $input;
// <script>alert("XSS")</script> — would execute in the browser

// With escape (SAFE):
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
// &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt; — displayed as text
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

$data = "O'Brian said: \"Hello\"";

echo htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
// O'Brian said: &quot;Hello&quot;

echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
// O&#039;Brian said: &quot;Hello&quot;

// Always use ENT_QUOTES for maximum security
```

### `htmlspecialchars()` vs `htmlentities()`

- **`htmlspecialchars()`**: Converte apenas `&`, `"`, `'`, `<`, `>` — o essencial para segurança
- **`htmlentities()`**: Converte **todos** os caracteres que possuem entidade HTML equivalente

```php
<?php

$text = 'Action & Heart';

echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
// Action & Heart (accented characters preserved)

echo htmlentities($text, ENT_QUOTES, 'UTF-8');
// Action &amp; Heart (no entities — these are ASCII-safe chars)
```

**Dica:** Para XSS em saída HTML, `htmlspecialchars()` é suficiente e mais previsível. Reserve `htmlentities()` para ASCII puro (emails antigos).

### `strip_tags()` — Remover Tags HTML

```php
<?php

$html = '<p>This is a <strong>text</strong> with a <a href="#">link</a>.</p>';

// Remove all tags
echo strip_tags($html);
// This is a text with a link.

// Allow specific tags
echo strip_tags($html, '<strong><em>');
// This is a <strong>text</strong> with a link.
```

**Cuidado:** `strip_tags()` não substitui `htmlspecialchars()` — remove tags mas não escapa. Para contexto HTML, sempre escape.

### Função Auxiliar para Templates

```php
<?php

/**
 * Escapes a value for safe HTML output.
 * If the value is null, returns an empty string.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// In templates:
$user = ['name' => '<b>John</b>', 'bio' => 'Dev & "ethical" hacker'];
echo '<h1>' . e($user['name']) . '</h1>';
echo '<p>' . e($user['bio']) . '</p>';
// <h1>&lt;b&gt;John&lt;/b&gt;</h1>
// <p>Dev &amp; &quot;ethical&quot; hacker</p>
```

---

## Utilidades: nl2br, wordwrap

### `nl2br()` — Converter Quebras de Linha em `<br>`

```php
<?php

$comment = "First line.\nSecond line.\nThird line.";

echo nl2br($comment);
// Primeira linha.<br />
// Segunda linha.<br />
// Terceira linha.
```

Para usar `<br>` (XHTML) em vez de `<br />`:

```php
<?php

echo nl2br($comment, false); // <br> em vez de <br />
```

### `wordwrap()` — Quebrar Linhas por Comprimento

```php
<?php

$text = 'The quick brown fox jumps over the lazy dog.';

echo wordwrap($text, 20, "<br />\n");
/*
O rápido cachorro
marrom pula sobre o
gato preguiçoso.
*/

// Com corte forçado (quarto parâmetro true):
echo wordwrap('AVeryyyyyyyyyyyyyyLongURL', 15, "<br />\n", true);
// URLMuitoooooooo
// ooooooooLonga
```

---

## Expressões Regulares Básicas

Regex são padrões de busca. Em PHP moderno, use as funções PCRE (`preg_*`).

### `preg_match()` — Buscar Padrão

```php
<?php

$email = 'user@domain.com.br';

// Check email format (simplified)
$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (preg_match($pattern, $email)) {
    echo 'Valid email';
} else {
    echo 'Invalid email';
}
```

### Capturar Grupos

```php
<?php

$date = 'Date: 2026-08-04';

if (preg_match('/Date: (\d{4})-(\d{2})-(\d{2})/', $date, $matches)) {
    print_r($matches);
    /*
    Array
    (
        [0] => Date: 2026-08-04
        [1] => 2026  // year
        [2] => 08    // month
        [3] => 04    // day
    )
    */
    echo "Year: {$matches[1]}, Month: {$matches[2]}, Day: {$matches[3]}";
    // Year: 2026, Month: 08, Day: 04
}
```

### `preg_match_all()` — Todas as Ocorrências

```php
<?php

$html = '<a href="/home">Home</a> <a href="/about">About</a> <a href="/contact">Contact</a>';

preg_match_all('/href="([^"]+)"/', $html, $matches);
print_r($matches[1]); // ['/home', '/about', '/contact']
```

### `preg_replace()` — Substituir com Regex

```php
<?php

// Remove everything that is not a digit (e.g., format phone)
$phone = '(555) 98765-4321';
$onlyNumbers = preg_replace('/\D/', '', $phone);
echo $onlyNumbers; // 555987654321

// Mask part of an email
$email = 'secret.user@provider.com.br';
$masked = preg_replace('/(.{3}).*(@.*)/', '$1***$2', $email);
echo $masked; // sec***@provider.com.br

// Replace multiple spaces with a single one
$text = 'PHP     is         awesome';
$clean = preg_replace('/\s+/', ' ', $text);
echo $clean; // PHP is awesome
```

### `preg_split()` — Dividir com Regex

```php
<?php

$csv = 'John, Mary; Peter| Anna, Bea';
$names = preg_split('/[,;|]\s*/', $csv);
print_r($names); // ['John', 'Mary', 'Peter', 'Anna', 'Bea']
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

**Cuidado:** `str_split()` trabalha com **bytes**, não com caracteres. Para UTF-8, use `mb_str_split()`.

### `grapheme_str_split()` — PHP 8.4+

**PHP 8.4+** — Divide considerando **grapheme clusters** (caracteres visuais compostos, como emojis e caracteres acentuados):

```php
<?php

// str_split fails with multibyte characters:
$str_split_result = str_split('café');
print_r($str_split_result); // ['c', 'a', 'f', '�', '�'] — corrupted!

// mb_str_split works for basic multibyte:
print_r(mb_str_split('café')); // ['c', 'a', 'f', 'é'] — OK

// grapheme_str_split handles everything, including compound emojis:
$flag = '🇧🇷';  // Brazilian flag (2 code points combined)
echo grapheme_strlen($flag) . PHP_EOL;   // 1 (one grapheme cluster)
print_r(grapheme_str_split($flag));      // ['🇧🇷']

// Family emoji:
$family = '👨‍👩‍👧';  // family (4 emojis combined with ZWJ)
echo grapheme_strlen($family) . PHP_EOL;    // 1
print_r(grapheme_str_split($family));       // ['👨‍👩‍👧']
```

**Dica:** `grapheme_str_split()` é a função mais segura para dividir strings com emojis ou caracteres combinados.

---

## Comparação de Strings

### `==` vs `===`

- `==` compara com **coerção de tipos**
- `===` compara sem coerção (valor e tipo)

```php
<?php

var_dump('123' == 123);    // bool(true)  — coerção: string '123' vira int 123
var_dump('123' === 123);   // bool(false) — tipos diferentes

var_dump(0 == '');         // bool(true)  — WARNING! '' is converted to 0
var_dump(0 === '');        // bool(false)
var_dump(0 == 'zero');     // bool(true)  — 'zero' is non-numeric, becomes 0
var_dump(0 === 'zero');    // bool(false)
```

**Cuidado:** Compare com `===` ao checar `false`, `0`, `''` ou `null` — `==` dá resultados inesperados.

### `strcmp()` — Comparação Binária

```php
<?php

var_dump(strcmp('abc', 'abc'));  // int(0)  — equal
var_dump(strcmp('abc', 'abd'));  // int(-1) — 'abc' < 'abd'
var_dump(strcmp('abd', 'abc'));  // int(1)  — 'abd' > 'abc'

// Case-sensitive
var_dump(strcmp('ABC', 'abc'));  // int(-1)
```

### `strcasecmp()` — Comparação Case-Insensitive

```php
<?php

var_dump(strcasecmp('ABC', 'abc')); // int(0) — equal, ignoring case
```

### `strnatcmp()` — Comparação com Ordem Natural

Ordena strings que contêm números de forma intuitiva (1, 2, 3, ..., 10, 11, em vez de 1, 10, 11, 2, 3...):

```php
<?php

$files = ['img10.jpg', 'img2.jpg', 'img1.jpg', 'img20.jpg'];

usort($files, 'strnatcmp');
print_r($files); // ['img1.jpg', 'img2.jpg', 'img10.jpg', 'img20.jpg']
```

---

## Referências

- [Documentação oficial: Strings](https://www.php.net/manual/en/language.types.string.php)
- [Funções de String](https://www.php.net/manual/en/ref.strings.php)
- [Heredoc / Nowdoc](https://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc)
- [str_contains() — PHP 8.0](https://www.php.net/manual/en/function.str-contains.php)
- [str_starts_with() — PHP 8.0](https://www.php.net/manual/en/function.str-starts-with.php)
- [str_ends_with() — PHP 8.0](https://www.php.net/manual/en/function.str-ends-with.php)
- [htmlspecialchars()](https://www.php.net/manual/en/function.htmlspecialchars.php)
- [Funções Multibyte (mbstring)](https://www.php.net/manual/en/book.mbstring.php)
- [mb_trim() — PHP 8.4](https://www.php.net/manual/en/function.mb-trim.php)
- [mb_ucfirst() — PHP 8.4](https://www.php.net/manual/en/function.mb-ucfirst.php)
- [grapheme_str_split() — PHP 8.4](https://www.php.net/manual/en/function.grapheme-str-split.php)
- [Expressões Regulares PCRE](https://www.php.net/manual/en/book.pcre.php)
- [sprintf()](https://www.php.net/manual/en/function.sprintf.php)
- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

---

> **Capítulo anterior:** [07 — Arrays](./07-arrays.md)
> **Próximo capítulo:** [09 — Programação Orientada a Objetos (Parte 1)](./09-oop.md)
