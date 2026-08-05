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

// E_NOTICE — variável não definida
echo $undefinedVariable;
// Notice: Undefined variable: undefinedVariable

// E_WARNING — include de arquivo inexistente
include 'arquivo_inexistente.php';
// Warning: include(arquivo_inexistente.php): Failed to open stream

// E_DEPRECATED — função obsoleta
// (exemplo hipotetico, funcoes antigas sao marcadas como deprecated)
// Deprecated: Function nome_da_funcao() is deprecated

// E_ERROR — chamar função inexistente (em versoes antigas, hoje é Error/Throwable)
// funcaoQueNaoExiste(); — Fatal error
```

### Exceções vs Erros

Desde o PHP 7.0, a maioria dos erros fatais foram convertidos em exceções que implementam `Throwable`. Você pode usar `try/catch` para a maioria dos erros:

```php
<?php

try {
    // TypeError: funcao espera int, recebeu string
    $result = array_sum('nao e um array');
} catch (TypeError $e) {
    echo "TypeError capturado: " . $e->getMessage() . PHP_EOL;
}
```

---

## Configuração de Exibição de Erros

### `error_reporting()` — Definir Nível de Reporte

```php
<?php

// Ambiente de desenvolvimento: show TUDO
error_reporting(E_ALL);

// Ambiente de producao: esconder notices, warnings e deprecated
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Apenas erros fatais
error_reporting(E_ERROR);

// Desligar (NAO recomendado!)
error_reporting(0);
```

### `ini_set()` — Configurações em Tempo de Execução

```php
<?php

// MOSTRAR erros na tela (desenvolvimento)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// ESCONDER erros da tela (producao) — mas logar no arquivo
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/php/php_errors.log');
```

### Configuração Ideal por Ambiente

```php
<?php

// ====================
// DESENVOLVIMENTO
// ====================
function setupDevEnvironment(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '0');
}

// ====================
// PRODUCAO
// ====================
function setupProductionEnvironment(): void
{
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Detectar pelo hostname ou variavel de ambiente
if (getenv('APP_ENV') === 'production') {
    setupProductionEnvironment();
} else {
    setupDevEnvironment();
}
```

Também é possível configurar no `php.ini`:

```ini
; php.ini — Desenvolvimento
error_reporting = E_ALL
display_errors = On
display_startup_errors = On
log_errors = Off

; php.ini — Produção
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
        throw new DivisionByZeroError('Divisao por zero nao permitida');
    }
    return $dividend / $divisor;
}

try {
    $result = divide(10, 0);
    echo "Resultado: {$result}";
} catch (DivisionByZeroError $e) {
    echo "Erro: " . $e->getMessage() . PHP_EOL;
    echo "Arquivo: " . $e->getFile() . PHP_EOL;
    echo "Linha: " . $e->getLine() . PHP_EOL;
} finally {
    echo "Bloco finally: sempre executado!" . PHP_EOL;
}

// Saida:
// Erro: Divisao por zero nao permitida
// Arquivo: /path/to/script.php
// Linha: 6
// Bloco finally: sempre executado!
```

### O Bloco `finally`

`finally` roda **sempre**, não importa se:
- A exceção ter sido lançada ou não
- A exceção ter sido capturada ou não
- Um `return` ter sido executado dentro do `try`

```php
<?php

function readFile(string $path): string
{
    $handle = null;
    try {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Nao foi possivel abrir: {$path}");
        }
        $content = fread($handle, filesize($path));
        return $content;
    } finally {
        // Garante que o arquivo sempre sera fechado
        if ($handle && is_resource($handle)) {
            fclose($handle);
            echo "[Arquivo fechado]" . PHP_EOL;
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
            throw new InvalidArgumentException('ID deve ser positivo');
        }

        $response = @file_get_contents("https://api.exemplo.com/users/{$id}");

        if ($response === false) {
            throw new RuntimeException('Falha na conexao com a API');
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Resposta JSON invalida', json_last_error());
        }

        return $data;
    }
}

$client = new ApiClient();

try {
    $user = $client->findUser(-1);
} catch (InvalidArgumentException $e) {
    echo "Erro de validacao: " . $e->getMessage() . PHP_EOL;
    // logica especifica para argumentos invalidos
} catch (RuntimeException $e) {
    echo "Erro de runtime: " . $e->getMessage() . PHP_EOL;
    // logica para falhas de rede/IO
} catch (JsonException $e) {
    echo "Erro de JSON: " . $e->getMessage() . PHP_EOL;
    // logica para fallback ou retry
} catch (Throwable $e) {
    // Captura qualquer outra excecao ou erro (fallback generico)
    echo "Erro inesperado: " . $e->getMessage() . PHP_EOL;
    error_log($e->getTraceAsString());
}
```

⚠️ **Cuidado:** A ordem dos `catch` importa! Sempre coloque as exceções mais **específicas** primeiro e as mais **genéricas** por último. Um `catch (Throwable $e)` captura todas e deve vir por último.

### Múltiplas Exceções no Mesmo Catch (PHP 7.1+)

```php
<?php

try {
    // codigo que pode lancar varias excecoes
} catch (InvalidArgumentException | DivisionByZeroError | RangeException $e) {
    // Tratamento comum para esses tres tipos
    echo "Erro: " . $e->getMessage() . PHP_EOL;
}
```

---

## Exceções Customizadas

Criar suas próprias exceções é uma excelente prática para códigos de domínio expressivos:

```php
<?php

// Excecao base do dominio
class DomainException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getTitulo(): string
    {
        return 'Erro de Dominio';
    }
}

// Excecoes especificas
class InsufficientBalanceException extends DomainException
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $requestedAmount,
    ) {
        $difference = number_format($this->requestedAmount - $this->currentBalance, 2, ',', '.');
        parent::__construct(
            "Saldo insuficiente. Saldo: R$ {$this->currentBalance}, " .
            "Solicitado: R$ {$this->requestedAmount}. " .
            "Faltam: R$ {$difference}"
        );
    }

    public function getTitulo(): string
    {
        return 'Saldo Insuficiente';
    }
}

class AccountBlockedException extends DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct("Account bloqueada: {$reason}");
    }
}

// Usando as excecoes customizadas
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
            throw new AccountBlockedException('Account sob investigacao');
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
    echo $e->getTitulo() . ': ' . $e->getMessage() . PHP_EOL;
    // Saldo Insuficiente: Saldo insuficiente. Saldo: R$ 100, ...
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
            "Falha ao save usuario: {$dataPayload['name']}",
            0,
            $e, // encadeia a excecao original
        );
    }
}

try {
    saveToDatabase(['name' => 'Joao']);
} catch (RepositoryException $e) {
    echo $e->getMessage() . PHP_EOL;
    echo "Causa raiz: " . $e->getPrevious()->getMessage() . PHP_EOL;
}
// Falha ao save usuario: Joao
// Causa raiz: Connection refused
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
    throw new RuntimeException('Algo deu errado', 500);
} catch (Throwable $e) {
    echo "Mensagem: " . $e->getMessage() . PHP_EOL;
    echo "Codigo: "   . $e->getCode() . PHP_EOL;
    echo "Arquivo: "  . $e->getFile() . PHP_EOL;
    echo "Linha: "    . $e->getLine() . PHP_EOL;

    // Stack trace resumido
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

// Ternario
$name = $input['name'] ?? throw new InvalidArgumentException('Nome obrigatorio');

// match
$status = match ($code) {
    200, 201 => 'sucesso',
    400 => 'erro de validacao',
    404 => 'nao encontrado',
    default => throw new UnexpectedValueException("Codigo HTTP desconhecido: {$code}"),
};

// arrow function
$validate = fn(string $email): string => filter_var($email, FILTER_VALIDATE_EMAIL)
    ? $email
    : throw new InvalidArgumentException("Email invalido: {$email}");

echo $validate('user@domain.com');

// coalescencia nula
$config = ['host' => 'localhost'];
$port = $config['porta'] ?? throw new RuntimeException('Configuracao porta ausente');
```

### Exemplo Prático — Validação em Constructor

```php
<?php

class Email
{
    public function __construct(private string $value)
    {
        filter_var($this->value, FILTER_VALIDATE_EMAIL)
            ?: throw new InvalidArgumentException("Email invalido: {$this->value}");
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

// $email = new Email('invalido'); // InvalidArgumentException
$email = new Email('user@domain.com');
echo $email; // user@domain.com
```

💡 **Dica:** `throw` como expressão reduz drasticamente `if`/`else` de validação, tornando o código mais declarativo.

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
  Arquivo: {$file}:{$line}
  Trace:
{$e->getTraceAsString()}
----------------------------------------
LOG;

    error_log($log, 3, __DIR__ . '/logs/exceptions.log');

    // Retornar uma resposta amigavel ao usuario
    http_response_code(500);
    echo json_encode([
        'error'  => 'Erro interno do servidor',
        'code' => $e->getCode(),
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

set_exception_handler('exceptionHandler');

// A partir daqui, excecoes nao capturadas chamam exceptionHandler()
// throw new RuntimeException('Teste de falha global');
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
        // Este nivel de erro nao esta configurado para reporte
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler('errorHandler');

// Agora notices e warnings viram excecoes capturaveis:
try {
    echo $variavelNaoDefinida;
} catch (ErrorException $e) {
    echo "Erro convertido: " . $e->getMessage() . PHP_EOL;
}
```

---

## get_error_handler() e get_exception_handler() — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Estas novas funções retornam, respectivamente, o handler de erros atual e o handler de exceções atual registrados com `set_error_handler()` e `set_exception_handler()`. São úteis para **inspecionar**, **restaurar temporariamente** ou **encadear** handlers sem precisar armazená-los manualmente em variáveis:

```php
<?php

// Registrar um handler personalizado
set_error_handler(function (int $severity, string $msg, string $file, int $line): bool {
    echo "[CUSTOM] {$msg} em {$file}:{$line}" . PHP_EOL;
    return true;
});

// PHP 8.5+: Obter o handler atual
$handler = get_error_handler();
var_dump($handler); // object(Closure)#1 (1) { ... }

// Restaurar o handler padrao e depois voltar ao personalizado
$previous = get_error_handler();
restore_error_handler(); // volta ao handler padrao do PHP

trigger_error('Usando handler padrao', E_USER_NOTICE);
// PHP Notice: Usando handler padrao in ...

// Reaplicar o handler anterior
set_error_handler($previous);
trigger_error('Usando handler personalizado novamente', E_USER_NOTICE);
// [CUSTOM] Usando handler personalizado novamente em ...

// Tambem funciona com excecoes
set_exception_handler(fn(Throwable $e) => error_log($e->getMessage()));
$excHandler = get_exception_handler();
var_dump($excHandler); // object(Closure)#2 (1) { ... }
```

### Caso de Uso Avançado — Middleware de Error Handling

```php
<?php

function comErrorHandlerTemporario(callable $fn, callable $tempHandler): mixed
{
    $previous = get_error_handler();   // PHP 8.5+
    set_error_handler($tempHandler);

    try {
        return $fn();
    } finally {
        // Restaura o handler original
        if ($previous !== null) {
            set_error_handler($previous);
        } else {
            restore_error_handler();
        }
    }
}

// Uso: execute codigo com um handler silencioso temporario
comErrorHandlerTemporario(
    fn() => trigger_error('ignorar este warning', E_USER_WARNING),
    fn() => true, // handler silencioso
);
// Nenhuma saida — o warning foi suprimido
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

// PHP 8.5: o erro mostrara todo o caminho:
// executeTask() -> processData() -> nonExistentFunction()
```

💡 **Dica:** O backtrace em fatal errors é **automático** no PHP 8.5 — você não precisa ativar nenhuma configuração especial. Em produção, certifique-se de que `display_errors` esteja desligado para não expor o stack trace para o usuário final.

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
            "Ano de nascimento ({$birthYear}) maior que o ano atual ({$currentYear})",
            E_USER_WARNING,
        );
        return 0;
    }

    if ($birthYear < 1900) {
        trigger_error(
            "Ano de nascimento muito antigo: {$birthYear}",
            E_USER_NOTICE,
        );
    }

    return $currentYear - $birthYear;
}

$age = calculateAge(2050); // Warning: ano maior que atual
echo "Idade: {$age}" . PHP_EOL; // 0

$age = calculateAge(1850); // Notice: ano muito antigo
echo "Idade: {$age}" . PHP_EOL; // 176
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

    error_log("[{$level}] {$msg} em {$file}:{$line}");

    if ($severity === E_USER_ERROR) {
        echo json_encode(['erro_fatal' => $msg]);
        exit(1);
    }

    return true; // nao executa o handler padrao do PHP
}

set_error_handler('myErrorHandler');

trigger_error('Configuracao obsoleta detectada', E_USER_DEPRECATED);
trigger_error('Funcionalidade X sera removida em v3.0', E_USER_DEPRECATED);
```

---

## Atributo `#[\Deprecated]` (PHP 8.4+)

**PHP 8.4+** — O atributo `#[\Deprecated]` substitui o antigo `@deprecated` do PHPDoc, fornecendo uma maneira padronizada e **verificável em tempo de compilação** de marcar funções, métodos e constantes como depreciadas:

```php
<?php

class LegacyLibrary
{
    #[\Deprecated(
        message: 'Use getUserPorId() em vez disso',
        since: '2.0.0',
    )]
    public function findUser(int $id): ?array
    {
        // implementacao antiga
        return null;
    }

    public function getUserPorId(int $id): ?array
    {
        // nova implementacao
        return null;
    }
}

class Formatter
{
    #[\Deprecated(
        message: 'Use Metodo::format() no lugar',
        since: '3.5',
    )]
    public const FORMATO_ANTIGO = 'Y-m-d';

    public const FORMATO_NOVO = 'd/m/Y';
}

$lib = new LegacyLibrary();

// Chamar metodo depreciado emite E_USER_DEPRECATED:
// $lib->findUser(1);
// Deprecated: LegacyLibrary::findUser() is deprecated,
// Use getUserPorId() em vez disso
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

// Em desenvolvimento, convert deprecated em excecao
set_error_handler(function (int $severity, string $msg): bool {
    if ($severity === E_USER_DEPRECATED || $severity === E_DEPRECATED) {
        throw new ErrorException($msg, 0, $severity);
    }
    return false;
});

class Service
{
    #[\Deprecated(message: 'Use ServiceNovo', since: '3.0')]
    public function execute(): string
    {
        return 'OK (antigo)';
    }
}

try {
    $s = new Service();
    $s->execute(); // Lanca ErrorException em dev — voce nao esquecera de migrar!
} catch (ErrorException $e) {
    echo "Migracao pendente: " . $e->getMessage() . PHP_EOL;
}
```

💡 **Dica:** Configure seu ambiente de desenvolvimento ou pipeline de CI para converter `E_USER_DEPRECATED` em exceções. Isso garante que sua equipe encontre e migre código depreciado antes do deploy.

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

// Modo 0: log padrao do sistema
error_log('Sistema iniciado com sucesso');

// Modo 3: gravar em arquivo especifico
error_log('Erro ao connect ao banco de dados' . PHP_EOL, 3, __DIR__ . '/logs/banco.log');

// Modo 1: enviar por email (use com cautela em producao!)
// error_log('Erro critico detectado', 1, 'admin@exemplo.com');
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

// Uso:
$logger = new Logger(__DIR__ . '/logs/app.log');

$logger->info('User autenticado', ['user_id' => 42]);
$logger->warning('Tentativa de login falhou', ['ip' => '192.168.1.1']);
$logger->error('Falha ao process pagamento', ['pedido_id' => 789]);
$logger->debug('Query executada', ['sql' => 'SELECT ...', 'tempo_ms' => 12.5]);

// Conteudo do log:
// [2026-08-04 10:30:00.123] [INFO] User autenticado {"user_id":42}
// [2026-08-04 10:30:05.456] [WARNING] Tentativa de login falhou {"ip":"192.168.1.1"}
// [2026-08-04 10:30:10.789] [ERROR] Falha ao process pagamento {"pedido_id":789}
// [2026-08-04 10:30:15.012] [DEBUG] Query executada {"sql":"SELECT ...","tempo_ms":12.5}
```

---

## Boas Práticas

### 1. Nunca Mostre Erros em Produção

```php
<?php

// CORRETO: Ambiente de producao
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// INCORRETO: expoe stack trace para usuarios
// ini_set('display_errors', '1');
```

### 2. Use Exceções Específicas do Domínio

```php
<?php

// RUIM:
throw new Exception('Saldo insuficiente');

// BOM:
throw new InsufficientBalanceException(currentBalance: 100.0, requestedAmount: 500.0);
```

### 3. Não Suprima Erros com `@`

O operador `@` suprime erros, mas torna a depuração impossível. Prefira verificações explícitas:

```php
<?php

// RUIM:
$content = @file_get_contents('arquivo_que_pode_nao_existir.txt');

// BOM:
if (file_exists('arquivo.txt') && is_readable('arquivo.txt')) {
    $content = file_get_contents('arquivo.txt');
} else {
    // tratar ausencia do arquivo
}

// ALTERNATIVA BOM: try/catch com throw expression
$content = file_exists('arquivo.txt')
    ? file_get_contents('arquivo.txt')
    : throw new RuntimeException('Arquivo nao encontrado');
```

### 4. Sempre Capture com `Throwable` como Fallback

```php
<?php

try {
    // codigo que pode lancar qualquer coisa
} catch (InvalidArgumentException $e) {
    // especifico
} catch (RuntimeException $e) {
    // especifico
} catch (Throwable $e) {
    // fallback generico — nao deixe nenhum erro escapar
    error_log($e);
    echo 'Erro interno. Tente novamente mais tarde.';
}
```

### 5. Use `finally` para Limpeza de Recursos

```php
<?php

function processFile(string $path): array
{
    $handle = fopen($path, 'r');

    try {
        // processamento que pode lancar excecao
        $data = [];
        while (($line = fgetcsv($handle)) !== false) {
            $data[] = $line;
        }
        return $data;
    } finally {
        // Garante fechamento mesmo com excecao
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
            throw new InvalidArgumentException('Descricao deve ter ao menos 3 caracteres');
        }

        if ($this->date < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Data deve ser futura');
        }
    }
}

// Validacao no momento da criacao — falha rapido, sem estados invalidos
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

// RUIM:
error_log("Erro no pedido 789: pagamento recusado");

// BOM:
error_log(json_encode([
    'level'      => 'ERROR',
    'message'    => 'Payment recusado',
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

// RUIM: captura e engole
try {
    saveOrder($orderData);
} catch (Exception $e) {
    // silencioso — ninguem sabe que falhou
}

// BOM: capture onde pode tomar uma acao
try {
    saveOrder($orderData);
    notifyClient($orderData);
} catch (RepositoryException $e) {
    // Log e compensacao
    $logger->error('Falha ao save pedido', ['order' => $orderData]);
    throw $e; // relancar se nao puder recuperar
}
```

---

## 📚 Referências

- [Documentação oficial: Tratamento de Erros](https://www.php.net/manual/pt_BR/book.errorfunc.php)
- [Exceções — PHP Manual](https://www.php.net/manual/pt_BR/language.exceptions.php)
- [Constantes de Erro Predefinidas](https://www.php.net/manual/pt_BR/errorfunc.constants.php)
- [set_error_handler()](https://www.php.net/manual/pt_BR/function.set-error-handler.php)
- [set_exception_handler()](https://www.php.net/manual/pt_BR/function.set-exception-handler.php)
- [get_error_handler() — PHP 8.5](https://www.php.net/manual/pt_BR/function.get-error-handler.php)
- [get_exception_handler() — PHP 8.5](https://www.php.net/manual/pt_BR/function.get-exception-handler.php)
- [trigger_error()](https://www.php.net/manual/pt_BR/function.trigger-error.php)
- [error_log()](https://www.php.net/manual/pt_BR/function.error-log.php)
- [Interface Throwable](https://www.php.net/manual/pt_BR/class.throwable.php)
- [Atributo #[\Deprecated] — PHP 8.4](https://www.php.net/manual/pt_BR/class.deprecated.php)
- [Throw como Expressão — PHP 8.0](https://www.php.net/manual/pt_BR/migration80.new-features.php#migration80.new-features.core.throw-expr)
- [Backtraces em Fatal Errors — PHP 8.5](https://wiki.php.net/rfc/fatal_error_backtraces)
- [Hierarquia de Exceções SPL](https://www.php.net/manual/pt_BR/spl.exceptions.php)
- [ErrorException](https://www.php.net/manual/pt_BR/class.errorexception.php)
- [ini_set() — Runtime Configuration](https://www.php.net/manual/pt_BR/function.ini-set.php)

---

> **Capítulo anterior:** [09 — Programação Orientada a Objetos](09-oop.md)

---

**Fim do módulo intermediário de PHP!**
