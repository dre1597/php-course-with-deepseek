# 10 — Tratamento de Erros e Exceções

## Índice

1. [Tipos de Erro em PHP](#tipos-de-erro-em-php)
2. [Configuração de Exibição de Erros](#configuração-de-exibição-de-erros)
3. [Try, Catch, Finally](#try-catch-finally)
4. [Múltiplos Catch Blocks](#múltiplos-catch-blocks)
5. [Exceções Customizadas](#exceções-customizadas)
6. [A Interface Throwable](#a-interface-throwable)
7. [Throw como Expressão (PHP 8.0+)](#throw-como-expressão-php-80)
8. [Global Exception Handler](#global-exception-handler)
9. [get_error_handler() e get_exception_handler() (PHP 8.5+)](#get_error_handler-e-get_exception_handler-php-85)
10. [Backtraces em Fatal Errors (PHP 8.5+)](#backtraces-em-fatal-errors-php-85)
11. [trigger_error — Erros de Usuário](#trigger_error--erros-de-usuário)
12. [Atributo #[\Deprecated] (PHP 8.4+)](#atributo-deprecated-php-84)
13. [Logging Básico com error_log()](#logging-básico-com-error_log)
14. [Boas Práticas](#boas-práticas)
15. [Referências](#referências)

---

## Tipos de Erro em PHP

O PHP classifica erros em diferentes níveis de severidade. Conhecer cada um ajuda a configurar o ambiente:

### Níveis de Erro (Constantes)

| Constante | Valor | Descrição |
|-----------|-------|-----------|
| `E_ERROR` | 1 | Erros fatais em tempo de execução — a execução é interrompida |
| `E_WARNING` | 2 | Avisos em tempo de execução — não interrompem a execução |
| `E_PARSE` | 4 | Erros de sintaxe (parse) em tempo de compilação |
| `E_NOTICE` | 8 | Notificações — possíveis bugs no código (variável não definida, etc.) |
| `E_CORE_ERROR` | 16 | Erros fatais na inicialização do PHP |
| `E_CORE_WARNING` | 32 | Avisos na inicialização do PHP |
| `E_COMPILE_ERROR` | 64 | Erros fatais em tempo de compilação |
| `E_COMPILE_WARNING` | 128 | Avisos em tempo de compilação |
| `E_USER_ERROR` | 256 | Erros gerados pelo usuário (`trigger_error()`) |
| `E_USER_WARNING` | 512 | Avisos gerados pelo usuário |
| `E_USER_NOTICE` | 1024 | Notificações geradas pelo usuário |
| `E_STRICT` | 2048 | Sugestões de interoperabilidade (obsoleto desde PHP 8.0) |
| `E_RECOVERABLE_ERROR` | 4096 | Erro capturável (pode ser convertido em exceção) |
| `E_DEPRECATED` | 8192 | Avisos de funcionalidades depreciadas |
| `E_USER_DEPRECATED` | 16384 | Depreciações geradas pelo usuário |
| `E_ALL` | 32767 | Todos os erros e avisos |

### Exemplo de Cada Nível

```php
<?php

// E_NOTICE — undefined variable
echo $undefinedVariable;
// Notice: Undefined variable: undefinedVariable

// E_WARNING — include de arquivo inexistente
include 'nonexistent_file.php';
// Warning: include(nonexistent_file.php): Failed to open stream

// E_DEPRECATED — obsolete function
// (hypothetical example, old functions are marked as deprecated)
// Deprecated: Function nome_da_funcao() is deprecated

// E_ERROR — calling nonexistent function (in older versions, now it's Error/Throwable)
// nonexistentFunction(); — Fatal error
```

### Exceções vs Erros

Desde o PHP 7.0, a maioria dos erros fatais foram convertidos em exceções que implementam `Throwable`. Você pode usar `try/catch` para a maioria dos erros:

```php
<?php

try {
    // TypeError: function expects int, received string
    $result = array_sum('not an array');
} catch (TypeError $e) {
    echo "TypeError caught: " . $e->getMessage() . PHP_EOL;
}
```

---

## Configuração de Exibição de Erros

### `error_reporting()` — Definir Nível de Reporte

```php
<?php

// Development environment: show EVERYTHING
error_reporting(E_ALL);

// Production environment: hide notices, warnings and deprecated
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Only fatal errors
error_reporting(E_ERROR);

// Turn off (NOT recommended!)
error_reporting(0);
```

### `ini_set()` — Configurações em Tempo de Execução

```php
<?php

// SHOW errors on screen (development)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// HIDE errors from screen (production) — but log to file
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php/php_errors.log');
```

### Configuração Ideal por Ambiente

```php
<?php

// ====================
// DEVELOPMENT
// ====================
function setupDevEnvironment(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '0');
}

// ====================
// PRODUCTION
// ====================
function setupProductionEnvironment(): void
{
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Detect by hostname or environment variable
if (getenv('APP_ENV') === 'production') {
    setupProductionEnvironment();
} else {
    setupDevEnvironment();
}
```

Também é possível configurar no `php.ini`:

```ini
; php.ini — Development
error_reporting = E_ALL
display_errors = On
display_startup_errors = On
log_errors = Off

; php.ini — Production
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

---

## Try, Catch, Finally

O bloco `try/catch/finally` é o mecanismo primário para capturar e tratar exceções:

```php
<?php

function divide(float $dividend, float $divisor): float
{
    if ($divisor === 0.0) {
        throw new DivisionByZeroError('Division by zero not allowed');
    }
    return $dividend / $divisor;
}

try {
    $result = divide(10, 0);
    echo "Result: {$result}";
} catch (DivisionByZeroError $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
} finally {
    echo "finally block: always executed!" . PHP_EOL;
}

// Output:
// Error: Division by zero not allowed
// File: /path/to/script.php
// Line: 6
// finally block: always executed!
```

### O Bloco `finally`

`finally` roda **sempre**, não importa se:
- A exceção ter sido lançada ou não
- A exceção ter sido capturada ou não
- Um `return` ter sido executado dentro do `try`

```php
<?php

function readFileSafe(string $path): string
{
    $handle = null;
    try {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open: {$path}");
        }
        $content = fread($handle, filesize($path));
        return $content;
    } finally {
        // Ensures the file will always be closed
        if ($handle && is_resource($handle)) {
            fclose($handle);
            echo "[File closed]" . PHP_EOL;
        }
    }
}
```

---

## Múltiplos Catch Blocks

É possível capturar diferentes tipos de exceção em blocos `catch` separados:

```php
<?php

class ApiClient
{
    public function findUser(int $id): array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID must be positive');
        }

        $response = @file_get_contents("https://api.exemplo.com/users/{$id}");

        if ($response === false) {
            throw new RuntimeException('API connection failed');
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Invalid JSON response', json_last_error());
        }

        return $data;
    }
}

$client = new ApiClient();

try {
    $user = $client->findUser(-1);
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage() . PHP_EOL;
    // specific logic for invalid arguments
} catch (RuntimeException $e) {
    echo "Runtime error: " . $e->getMessage() . PHP_EOL;
    // logic for network/IO failures
} catch (JsonException $e) {
    echo "JSON error: " . $e->getMessage() . PHP_EOL;
    // logic for fallback or retry
} catch (Throwable $e) {
    // Catch any other exception or error (generic fallback)
    echo "Unexpected error: " . $e->getMessage() . PHP_EOL;
    error_log($e->getTraceAsString());
}
```

A ordem dos `catch` importa! Sempre coloque as exceções mais **específicas** primeiro e as mais **genéricas** por último. Um `catch (Throwable $e)` captura todas e deve vir por último.

### Múltiplas Exceções no Mesmo Catch (PHP 7.1+)

```php
<?php

try {
    // code that may throw various exceptions
} catch (InvalidArgumentException | DivisionByZeroError | RangeException $e) {
    // Common handling for these three types
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
```

---

## Exceções Customizadas

Criar suas próprias exceções é uma excelente prática para códigos de domínio expressivos:

```php
<?php

// Base domain exception
class DomainException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getTitle(): string
    {
        return 'Domain Error';
    }
}

// Specific exceptions
class InsufficientBalanceException extends DomainException
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $requestedAmount,
    ) {
        $difference = number_format($this->requestedAmount - $this->currentBalance, 2, '.', ',');
        parent::__construct(
            "Insufficient balance. Balance: $ {$this->currentBalance}, " .
            "Requested: $ {$this->requestedAmount}. " .
            "Shortfall: $ {$difference}"
        );
    }

    public function getTitle(): string
    {
        return 'Insufficient Balance';
    }
}

class AccountBlockedException extends DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct("Account blocked: {$reason}");
    }
}

// Using custom exceptions
class Account
{
    private bool $blocked = false;

    public function __construct(
        private string $holder,
        private float $balance = 0.0,
    ) {}

    public function withdraw(float $value): void
    {
        if ($this->blocked) {
            throw new AccountBlockedException('Account under investigation');
        }

        if ($value > $this->balance) {
            throw new InsufficientBalanceException(
                currentBalance: $this->balance,
                requestedAmount: $value,
            );
        }

        $this->balance -= $value;
    }

    public function block(): void
    {
        $this->blocked = true;
    }
}

// Controller / Service
$account = new Account('Maria', 100.00);

try {
    $account->withdraw(500.00);
} catch (InsufficientBalanceException $e) {
    echo $e->getTitle() . ': ' . $e->getMessage() . PHP_EOL;
    // Insufficient Balance: Insufficient balance. Balance: $ 100, ...
} catch (AccountBlockedException $e) {
    echo $e->getMessage() . PHP_EOL;
} catch (DomainException $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

### Encadeamento de Exceções (Exception Chaining)

O terceiro parâmetro do construtor de `Exception` permite encadear exceções, preservando o contexto:

```php
<?php

class RepositoryException extends Exception {}

function saveToDatabase(array $dataPayload): void
{
    try {
        // Simulando falha na conexao PDO
        throw new PDOException('Connection refused');
    } catch (PDOException $e) {
        throw new RepositoryException(
            "Failed to save user: {$dataPayload['name']}",
            0,
            $e, // chain the original exception
        );
    }
}

try {
    saveToDatabase(['name' => 'Joao']);
} catch (RepositoryException $e) {
    echo $e->getMessage() . PHP_EOL;
    echo "Root cause: " . $e->getPrevious()->getMessage() . PHP_EOL;
}
// Failed to save user: Joao
// Root cause: Connection refused
```

---

## A Interface Throwable

Desde o PHP 7.0, a hierarquia de exceções é:

```
Throwable (interface)
├── Error (classe base para erros internos do PHP)
│   ├── TypeError
│   ├── ValueError
│   ├── ParseError
│   ├── AssertionError
│   ├── ArithmeticError
│   │   ├── DivisionByZeroError
│   ├── CompileError
│   └── ... outros erros fatais
└── Exception (classe base para exceções de usuário)
    ├── LogicException
    │   ├── InvalidArgumentException
    │   ├── DomainException
    │   └── ...
    ├── RuntimeException
    │   ├── OverflowException
    │   ├── UnderflowException
    │   └── ...
    └── ... (suas exceções customizadas)
```

### Métodos da Interface `Throwable`

| Método | Descrição |
|--------|-----------|
| `getMessage(): string` | Mensagem da exceção |
| `getCode(): int` | Código numérico |
| `getFile(): string` | Arquivo onde ocorreu |
| `getLine(): int` | Linha onde ocorreu |
| `getTrace(): array` | Stack trace como array |
| `getTraceAsString(): string` | Stack trace como string |
| `getPrevious(): ?Throwable` | Exceção anterior (encadeada) |
| `__toString(): string` | Representação como string |

```php
<?php

try {
    throw new RuntimeException('Something went wrong', 500);
} catch (Throwable $e) {
    echo "Message: " . $e->getMessage() . PHP_EOL;
    echo "Code: "   . $e->getCode() . PHP_EOL;
    echo "File: "  . $e->getFile() . PHP_EOL;
    echo "Line: "    . $e->getLine() . PHP_EOL;

    // Summarized stack trace
    $trace = $e->getTrace();
    foreach ($trace as $i => $frame) {
        $function = $frame['function'] ?? '???';
        $frameLine = $frame['line'] ?? '???';
        echo "  #{$i} {$function}() na linha {$frameLine}" . PHP_EOL;
    }
}
```

---

## Throw como Expressão (PHP 8.0+)

**PHP 8.0+** — `throw` agora é uma **expressão**, podendo ser usado em contextos que exigem um valor (ternários, arrow functions, `match`, `??`, etc.):

```php
<?php

// Ternary
$name = $input['name'] ?? throw new InvalidArgumentException('Name required');

// match
$status = match ($code) {
    200, 201 => 'success',
    400 => 'validation error',
    404 => 'not found',
    default => throw new UnexpectedValueException("Unknown HTTP status code: {$code}"),
};

// arrow function
$validate = fn(string $email): string => filter_var($email, FILTER_VALIDATE_EMAIL)
    ? $email
    : throw new InvalidArgumentException("Invalid email: {$email}");

echo $validate('user@domain.com');

// null coalescing
$config = ['host' => 'localhost'];
$port = $config['port'] ?? throw new RuntimeException('Port configuration missing');
```

### Exemplo Prático — Validação em Constructor

```php
<?php

class Email
{
    public function __construct(private string $value)
    {
        filter_var($this->value, FILTER_VALIDATE_EMAIL)
            ?: throw new InvalidArgumentException("Invalid email: {$this->value}");
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

// $email = new Email('invalid'); // InvalidArgumentException
$email = new Email('user@domain.com');
echo $email; // user@domain.com
```

`throw` como expressão reduz drasticamente `if`/`else` de validação, tornando o código mais declarativo.

---

## Global Exception Handler

`set_exception_handler()` define uma função global que será chamada para **qualquer exceção não capturada**:

```php
<?php

function exceptionHandler(Throwable $e): void
{
    $timestamp = date('Y-m-d H:i:s');
    $class = get_class($e);
    $message = $e->getMessage();
    $file  = $e->getFile();
    $line    = $e->getLine();

    $log = <<<LOG
[{$timestamp}] {$class}: {$message}
  File: {$file}:{$line}
  Trace:
{$e->getTraceAsString()}
----------------------------------------
LOG;

    error_log($log, 3, __DIR__ . '/logs/exceptions.log');

    // Return a friendly response to the user
    http_response_code(500);
    echo json_encode([
        'error'  => 'Internal server error',
        'code' => $e->getCode(),
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

set_exception_handler('exceptionHandler');

// From here on, uncaught exceptions call exceptionHandler()
// throw new RuntimeException('Global failure test');
```

### Combinando com Error Handler

Para capturar **erros** tradicionais (não exceções) como exceções:

```php
<?php

function errorHandler(
    int $severity,
    string $message,
    string $file,
    int $line,
): bool {
    if (!(error_reporting() & $severity)) {
        // This error level is not configured for reporting
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler('errorHandler');

// Now notices and warnings become catchable exceptions:
try {
    echo $undefinedVariable;
} catch (ErrorException $e) {
    echo "Converted error: " . $e->getMessage() . PHP_EOL;
}
```

---

## get_error_handler() e get_exception_handler() — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Estas novas funções retornam, respectivamente, o handler de erros atual e o handler de exceções atual registrados com `set_error_handler()` e `set_exception_handler()`. São úteis para **inspecionar**, **restaurar temporariamente** ou **encadear** handlers sem precisar armazená-los manualmente em variáveis:

```php
<?php

// Register a custom handler
set_error_handler(function (int $severity, string $msg, string $file, int $line): bool {
    echo "[CUSTOM] {$msg} in {$file}:{$line}" . PHP_EOL;
    return true;
});

// PHP 8.5+: Get the current handler
$handler = get_error_handler();
var_dump($handler); // object(Closure)#1 (1) { ... }

// Restore default handler and then switch back to the custom one
$previous = get_error_handler();
restore_error_handler(); // back to PHP default handler

trigger_error('Using default handler', E_USER_NOTICE);
// PHP Notice: Using default handler in ...

// Reapply the previous handler
set_error_handler($previous);
trigger_error('Using custom handler again', E_USER_NOTICE);
// [CUSTOM] Using custom handler again in ...

// Also works with exceptions
set_exception_handler(fn(Throwable $e) => error_log($e->getMessage()));
$excHandler = get_exception_handler();
var_dump($excHandler); // object(Closure)#2 (1) { ... }
```

### Caso de Uso Avançado — Middleware de Error Handling

```php
<?php

function withTemporaryErrorHandler(callable $fn, callable $tempHandler): mixed
{
    $previous = get_error_handler();   // PHP 8.5+
    set_error_handler($tempHandler);

    try {
        return $fn();
    } finally {
        // Restore original handler
        if ($previous !== null) {
            set_error_handler($previous);
        } else {
            restore_error_handler();
        }
    }
}

// Usage: run code with a temporary silent handler
withTemporaryErrorHandler(
    fn() => trigger_error('ignore this warning', E_USER_WARNING),
    fn() => true, // silent handler
);
// No output — the warning was suppressed
```

---

## Backtraces em Fatal Errors — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Erros fatais (fatal errors) agora incluem um **backtrace detalhado** na mensagem de erro padrão, além do simples "em arquivo X, linha Y". Antes do PHP 8.5, apenas exceções não capturadas mostravam o stack trace completo.

### Antes (PHP 8.4 e anteriores)

```
Fatal error: Call to undefined function nonExistentFunction() in /app/script.php on line 10
```

### Agora (PHP 8.5+)

```
Fatal error: Call to undefined function nonExistentFunction() in /app/script.php on line 10
Stack trace:
#0 /app/script.php(10): processData()
#1 /app/script.php(15): executeTask()
#2 /app/script.php(20): {main}
```

Isso facilita imensamente a depuração de erros fatais sem precisar de um debugger externo:

```php
<?php

function processData(): void
{
    nonExistentFunction();  // fatal error
}

function executeTask(): void
{
    processData();
}

executeTask();

// PHP 8.5: the error will show the full path:
// executeTask() -> processData() -> nonExistentFunction()
```

O backtrace em fatal errors é **automático** no PHP 8.5 — você não precisa ativar nenhuma configuração. Em produção, certifique-se de que `display_errors` esteja desligado para não expor o stack trace.

---

## trigger_error — Erros de Usuário

`trigger_error()` permite que seu código gere erros de usuário personalizados em tempo de execução. Esses erros são tratados pelo error handler configurado:

```php
<?php

function calculateAge(int $birthYear): int
{
    $currentYear = (int) date('Y');

    if ($birthYear > $currentYear) {
        trigger_error(
            "Birth year ({$birthYear}) greater than current year ({$currentYear})",
            E_USER_WARNING,
        );
        return 0;
    }

    if ($birthYear < 1900) {
        trigger_error(
            "Birth year too far in the past: {$birthYear}",
            E_USER_NOTICE,
        );
    }

    return $currentYear - $birthYear;
}

$age = calculateAge(2050); // Warning: birth year greater than current
echo "Age: {$age}" . PHP_EOL; // 0

$age = calculateAge(1850); // Notice: birth year too far in the past
echo "Age: {$age}" . PHP_EOL; // 176
```

### Níveis de Erro de Usuário

| Constante | Uso |
|-----------|-----|
| `E_USER_NOTICE` | Informação não crítica, possível uso incorreto |
| `E_USER_WARNING` | Algo suspeito, mas a execução continua |
| `E_USER_ERROR` | Erro fatal — a execução é interrompida |
| `E_USER_DEPRECATED` | Funcionalidade que será removida em versões futuras |

### Integrando com Error Handler Customizado

```php
<?php

function myErrorHandler(int $severity, string $msg, string $file, int $line): bool
{
    $levels = [
        E_USER_NOTICE     => 'NOTICE',
        E_USER_WARNING    => 'WARNING',
        E_USER_ERROR      => 'ERROR',
        E_USER_DEPRECATED => 'DEPRECATED',
    ];

    $level = $levels[$severity] ?? 'UNKNOWN';

    error_log("[{$level}] {$msg} in {$file}:{$line}");

    if ($severity === E_USER_ERROR) {
        echo json_encode(['fatal_error' => $msg]);
        exit(1);
    }

    return true; // don't run PHP's default handler
}

set_error_handler('myErrorHandler');

trigger_error('Outdated configuration detected', E_USER_DEPRECATED);
trigger_error('Feature X will be removed in v3.0', E_USER_DEPRECATED);
```

---

## Atributo `#[\Deprecated]` (PHP 8.4+)

**PHP 8.4+** — O atributo `#[\Deprecated]` substitui o antigo `@deprecated` do PHPDoc, fornecendo uma maneira padronizada e **verificável em tempo de compilação** de marcar funções, métodos e constantes como depreciadas:

```php
<?php

class LegacyLibrary
{
    #[\Deprecated(
        message: 'Use getUserById() instead',
        since: '2.0.0',
    )]
    public function findUser(int $id): ?array
    {
        // old implementation
        return null;
    }

    public function getUserById(int $id): ?array
    {
        // new implementation
        return null;
    }
}

class Formatter
{
    #[\Deprecated(
        message: 'Use Method::format() instead',
        since: '3.5',
    )]
    public const OLD_FORMAT = 'Y-m-d';

    public const NEW_FORMAT = 'd/m/Y';
}

$lib = new LegacyLibrary();

// Calling deprecated method emits E_USER_DEPRECATED:
// $lib->findUser(1);
// Deprecated: LegacyLibrary::findUser() is deprecated,
// Use getUserById() instead
```

### Funcionamento do `#[\Deprecated]`

- Ao chamar uma função/método marcado com `#[\Deprecated]`, o PHP emite um `E_USER_DEPRECATED`
- O atributo suporta dois parâmetros:
  - `message` — mensagem explicando o que usar no lugar
  - `since` — versão desde quando está depreciado
- Pode ser aplicado a funções, métodos, constantes de classe e propriedades

### Exemplo Migratório com Error Handler

```php
<?php

// In development, convert deprecated into exception
set_error_handler(function (int $severity, string $msg): bool {
    if ($severity === E_USER_DEPRECATED || $severity === E_DEPRECATED) {
        throw new ErrorException($msg, 0, $severity);
    }
    return false;
});

class Service
{
    #[\Deprecated(message: 'Use NewService', since: '3.0')]
    public function execute(): string
    {
        return 'OK (old)';
    }
}

try {
    $s = new Service();
    $s->execute(); // Throws ErrorException in dev — you won't forget to migrate!
} catch (ErrorException $e) {
    echo "Pending migration: " . $e->getMessage() . PHP_EOL;
}
```

Configure seu ambiente de desenvolvimento ou pipeline de CI para converter `E_USER_DEPRECATED` em exceções. Isso garante que código depreciado seja migrado antes do deploy.

---

## Logging Básico com error_log()

`error_log()` envia mensagens para o log de erro configurado no servidor ou para um arquivo específico:

### Modos de `error_log()`

| Tipo de Mensagem | Descrição |
|------------------|-----------|
| `0` (padrão) | Envia para o log do sistema (syslog ou arquivo configurado no php.ini) |
| `1` | Envia por email |
| `2` | Destino remoto (TCP) — raramente usado |
| `3` | Grava em arquivo específico |
| `4` | Envia para o SAPI (server API) |

### Exemplos

```php
<?php

// Mode 0: default system log
error_log('System started successfully');

// Mode 3: write to specific file
error_log('Database connection error' . PHP_EOL, 3, __DIR__ . '/logs/database.log');

// Mode 1: send by email (use cautiously in production!)
// error_log('Critical error detected', 1, 'admin@example.com');
```

### Logger Simples para Aplicações

```php
<?php

class Logger
{
    public function __construct(
        private string $file,
    ) {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function info(string $message, array $contextData = []): void
    {
        $this->log('INFO', $message, $contextData);
    }

    public function warning(string $message, array $contextData = []): void
    {
        $this->log('WARNING', $message, $contextData);
    }

    public function error(string $message, array $contextData = []): void
    {
        $this->log('ERROR', $message, $contextData);
    }

    public function debug(string $message, array $contextData = []): void
    {
        $this->log('DEBUG', $message, $contextData);
    }

    private function log(string $level, string $message, array $contextData): void
    {
        $timestamp = date('Y-m-d H:i:s.v');
        $contextDataStr = empty($contextData) ? '' : ' ' . json_encode($contextData, JSON_UNESCAPED_UNICODE);
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextDataStr}" . PHP_EOL;

        error_log($logLine, 3, $this->file);
    }
}

// Usage:
$logger = new Logger(__DIR__ . '/logs/app.log');

$logger->info('User authenticated', ['user_id' => 42]);
$logger->warning('Login attempt failed', ['ip' => '192.168.1.1']);
$logger->error('Payment processing failed', ['order_id' => 789]);
$logger->debug('Query executed', ['sql' => 'SELECT ...', 'time_ms' => 12.5]);

// Log contents:
// [2026-08-04 10:30:00.123] [INFO] User authenticated {"user_id":42}
// [2026-08-04 10:30:05.456] [WARNING] Login attempt failed {"ip":"192.168.1.1"}
// [2026-08-04 10:30:10.789] [ERROR] Payment processing failed {"order_id":789}
// [2026-08-04 10:30:15.012] [DEBUG] Query executed {"sql":"SELECT ...","time_ms":12.5}
```

---

## Boas Práticas

### 1. Nunca Mostre Erros em Produção

```php
<?php

// CORRECT: Production environment
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// INCORRECT: exposes stack trace to users
// ini_set('display_errors', '1');
```

### 2. Use Exceções Específicas do Domínio

```php
<?php

// BAD:
throw new Exception('Insufficient balance');

// GOOD:
throw new InsufficientBalanceException(currentBalance: 100.0, requestedAmount: 500.0);
```

### 3. Não Suprima Erros com `@`

O operador `@` suprime erros, mas torna a depuração impossível. Prefira verificações explícitas:

```php
<?php

// BAD:
$content = @file_get_contents('file_that_may_not_exist.txt');

// GOOD:
if (file_exists('file.txt') && is_readable('file.txt')) {
    $content = file_get_contents('file.txt');
} else {
    // handle missing file
}

// GOOD ALTERNATIVE: try/catch with throw expression
$content = file_exists('file.txt')
    ? file_get_contents('file.txt')
    : throw new RuntimeException('File not found');
```

### 4. Sempre Capture com `Throwable` como Fallback

```php
<?php

try {
    // code that may throw anything
} catch (InvalidArgumentException $e) {
    // specific
} catch (RuntimeException $e) {
    // specific
} catch (Throwable $e) {
    // generic fallback — don't let any error escape
    error_log($e);
    echo 'Internal error. Please try again later.';
}
```

### 5. Use `finally` para Limpeza de Recursos

```php
<?php

function processFile(string $path): array
{
    $handle = fopen($path, 'r');

    try {
        // processing that may throw exception
        $data = [];
        while (($line = fgetcsv($handle)) !== false) {
            $data[] = $line;
        }
        return $data;
    } finally {
        // Ensures closing even on exception
        if (is_resource($handle)) {
            fclose($handle);
        }
    }
}
```

### 6. Valide Dados com Exceções (Fail Fast)

```php
<?php

class Appointment
{
    public function __construct(
        private DateTimeImmutable $date,
        private string $description,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (mb_strlen($this->description) < 3) {
            throw new InvalidArgumentException('Description must be at least 3 characters');
        }

        if ($this->date < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Date must be in the future');
        }
    }
}

// Validation at creation time — fail fast, no invalid states
try {
    $appointment = new Appointment(new DateTimeImmutable('2020-01-01'), 'A');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

### 7. Logging Estruturado

Prefira logs com contexto estruturado (JSON) em vez de strings soltas — isso facilita análises automáticas:

```php
<?php

// BAD:
error_log("Error in order 789: payment declined");

// GOOD:
error_log(json_encode([
    'level'      => 'ERROR',
    'message'    => 'Payment declined',
    'order_id'   => 789,
    'timestamp'  => date('c'),
    'gateway'    => 'stripe',
    'code'       => 'card_declined',
], JSON_UNESCAPED_UNICODE));
```

### 8. Trate Exceções no Nível Adequado

Não capture exceções muito cedo se não puder tratá-las de forma significativa:

```php
<?php

// BAD: catch and swallow
try {
    saveOrder($orderData);
} catch (Exception $e) {
    // silent — no one knows it failed
}

// GOOD: catch where you can take action
try {
    saveOrder($orderData);
    notifyClient($orderData);
} catch (RepositoryException $e) {
    // Log and compensation
    $logger->error('Failed to save order', ['order' => $orderData]);
    throw $e; // rethrow if cannot recover
}
```

---

## Referências

- [Documentação oficial: Tratamento de Erros](https://www.php.net/manual/en/book.errorfunc.php)
- [Exceções — PHP Manual](https://www.php.net/manual/en/language.exceptions.php)
- [Constantes de Erro Predefinidas](https://www.php.net/manual/en/errorfunc.constants.php)
- [set_error_handler()](https://www.php.net/manual/en/function.set-error-handler.php)
- [set_exception_handler()](https://www.php.net/manual/en/function.set-exception-handler.php)
- [get_error_handler() — PHP 8.5](https://www.php.net/manual/en/function.get-error-handler.php)
- [get_exception_handler() — PHP 8.5](https://www.php.net/manual/en/function.get-exception-handler.php)
- [trigger_error()](https://www.php.net/manual/en/function.trigger-error.php)
- [error_log()](https://www.php.net/manual/en/function.error-log.php)
- [Interface Throwable](https://www.php.net/manual/en/class.throwable.php)
- [Atributo #[\Deprecated] — PHP 8.4](https://www.php.net/manual/en/class.deprecated.php)
- [Throw como Expressão — PHP 8.0](https://www.php.net/manual/en/migration80.new-features.php#migration80.new-features.core.throw-expr)
- [Backtraces em Fatal Errors — PHP 8.5](https://wiki.php.net/rfc/fatal_error_backtraces)
- [Hierarquia de Exceções SPL](https://www.php.net/manual/en/spl.exceptions.php)
- [ErrorException](https://www.php.net/manual/en/class.errorexception.php)
- [ini_set() — Runtime Configuration](https://www.php.net/manual/en/function.ini-set.php)

---

> **Capítulo anterior:** [09 — Programação Orientada a Objetos](./09-oop.md)

---

**Fim do módulo intermediário de PHP!**
