# 09 — Programação Orientada a Objetos em PHP (Parte 1)

## Índice

1. [Classes e Objetos](#classes-e-objetos)
2. [Propriedades](#propriedades)
3. [Métodos](#métodos)
4. [Constructor e Constructor Promotion](#constructor-e-constructor-promotion)
5. [Herança](#herança)
6. [Interfaces](#interfaces)
7. [Traits](#traits)
8. [Classes Abstratas](#classes-abstratas)
9. [Propriedades e Métodos Estáticos](#propriedades-e-métodos-estáticos)
10. [Constantes de Classe](#constantes-de-classe)
11. [Readonly: Propriedades e Classes](#readonly-propriedades-e-classes)
12. [Clonagem de Objetos](#clonagem-de-objetos)
13. [Property Hooks (PHP 8.4+)](#property-hooks-php-84)
14. [Asymmetric Visibility (PHP 8.4+)](#asymmetric-visibility-php-84)
15. [Final Property com Constructor Promotion (PHP 8.5+)](#final-property-com-constructor-promotion-php-85)
16. [Métodos Mágicos](#métodos-mágicos)
17. [Late Static Binding](#late-static-binding)
18. [Autoloading Básico](#autoloading-básico)
19. [Referências](#referências)

---

## Classes e Objetos

Uma **classe** é o molde (blueprint) que define propriedades e comportamentos. Um **objeto** é uma instância concreta da classe, criada com o operador `new`:

```php
<?php

class Produto
{
    public string $nome;
    public float $preco;
}

$p1 = new Produto();
$p1->nome  = 'Notebook';
$p1->preco = 3500.00;

$p2 = new Produto();
$p2->nome  = 'Mouse';
$p2->preco = 89.90;

echo "{$p1->nome}: R$ {$p1->preco}"; // Notebook: R$ 3500
```

### `$this` — Referência ao Objeto Atual

Dentro da classe, `$this` referencia a instância atual:

```php
<?php

class Mensagem
{
    public string $texto;

    public function exibir(): void
    {
        echo $this->texto;
    }
}

$m = new Mensagem();
$m->texto = 'Olá, mundo!';
$m->exibir(); // Olá, mundo!
```

---

## Propriedades

Propriedades armazenam o estado de um objeto. Os modificadores de visibilidade controlam o acesso:

| Modificador | Acesso |
|-------------|--------|
| `public` | Dentro e fora da classe, e em subclasses |
| `protected` | Dentro da classe e em subclasses |
| `private` | Apenas dentro da classe |

```php
<?php

class ContaBancaria
{
    private string $titular;
    private float $saldo = 0.0;
    protected string $tipo = 'corrente';

    public function __construct(string $titular)
    {
        $this->titular = $titular;
    }

    public function depositar(float $valor): void
    {
        if ($valor > 0) {
            $this->saldo += $valor;
        }
    }

    public function getSaldo(): float
    {
        return $this->saldo;
    }

    public function getTitular(): string
    {
        return $this->titular;
    }
}

$conta = new ContaBancaria('João Silva');
$conta->depositar(1000);
echo $conta->getSaldo();   // 1000

// $conta->saldo = 9999;   // Erro: propriedade private
// $conta->tipo = 'poupanca'; // Erro: propriedade protected
```

### Typed Properties (PHP 7.4+)

Propriedades tipadas são inicializadas com `null` por padrão (se nullable) ou devem ser inicializadas antes de acessadas:

```php
<?php

class Usuario
{
    public string $nome;
    public int $idade;
    public ?string $telefone = null;  // nullable, com valor default
    public bool $ativo = true;
}

$u = new Usuario();
$u->nome  = 'Maria';
$u->idade = 28;

// $u->nome = 123; // TypeError — esperava string

// Se tentarmos acessar sem inicializar (propriedade nao-nullable):
// $u2 = new Usuario();
// echo $u2->nome; // Erro: propriedade nao inicializada
```

💡 **Dica:** Sempre inicialize propriedades tipadas no constructor ou com valores default. Propriedades não inicializadas de tipos não-nullable causam `Error` fatal quando acessadas.

---

## Métodos

Métodos definem comportamentos. Seguem a mesma lógica de visibilidade das propriedades:

```php
<?php

class Calculadora
{
    public function somar(int $a, int $b): int
    {
        return $a + $b;
    }

    protected function validarOperacao(string $op): bool
    {
        return in_array($op, ['+', '-', '*', '/']);
    }

    private function log(string $mensagem): void
    {
        error_log("[Calculadora] {$mensagem}");
    }

    public function executar(string $op, int $a, int $b): int|float
    {
        if (!$this->validarOperacao($op)) {
            throw new InvalidArgumentException("Operacao invalida: {$op}");
        }

        $this->log("Executando {$a} {$op} {$b}");

        return match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $a / $b,
        };
    }
}

$calc = new Calculadora();
echo $calc->executar('*', 6, 7); // 42
// $calc->validarOperacao('*');   // Erro: metodo protected
// $calc->log('teste');           // Erro: metodo private
```

---

## Constructor e Constructor Promotion

### `__construct()`

O construtor roda ao instanciar a classe com `new`:

```php
<?php

class Livro
{
    private string $titulo;
    private string $autor;
    private int $ano;

    public function __construct(string $titulo, string $autor, int $ano)
    {
        $this->titulo = $titulo;
        $this->autor  = $autor;
        $this->ano    = $ano;
    }

    public function getDescricao(): string
    {
        return "{$this->titulo}, por {$this->autor} ({$this->ano})";
    }
}

$livro = new Livro('PHP Moderno', 'Ana Costa', 2026);
echo $livro->getDescricao(); // PHP Moderno, por Ana Costa (2026)
```

### Constructor Promotion (PHP 8.0+)

Sintaxe concisa que declara **e atribui** propriedades nos parâmetros do constructor:

```php
<?php

// Antes (PHP < 8.0):
class LivroAntigo
{
    private string $titulo;
    private string $autor;
    private int $ano;

    public function __construct(string $titulo, string $autor, int $ano)
    {
        $this->titulo = $titulo;
        $this->autor  = $autor;
        $this->ano    = $ano;
    }
}

// Depois (PHP 8.0+):
class Livro
{
    public function __construct(
        private string $titulo,
        private string $autor,
        private int $ano,
    ) {}

    public function getDescricao(): string
    {
        return "{$this->titulo}, por {$this->autor} ({$this->ano})";
    }
}

$livro = new Livro('PHP Moderno', 'Ana Costa', 2026);
echo $livro->getDescricao(); // PHP Moderno, por Ana Costa (2026)
```

### Constructor Promotion com Valores Default

```php
<?php

class Configuracao
{
    public function __construct(
        private string $host = 'localhost',
        private int $porta = 3306,
        private string $usuario = 'root',
        private bool $debug = false,
    ) {}

    public function getDsn(): string
    {
        return "mysql:host={$this->host};port={$this->porta}";
    }
}

$config = new Configuracao(host: 'db.producao', debug: true);
echo $config->getDsn(); // mysql:host=db.producao;port=3306
```

### Corpo do Constructor com Promotion

Você pode ter lógica adicional no constructor mesmo usando promotion:

```php
<?php

class Pedido
{
    private DateTimeImmutable $criadoEm;

    public function __construct(
        private string $id,
        private array $itens,
        private float $total,
    ) {
        $this->criadoEm = new DateTimeImmutable();
        $this->validar();
    }

    private function validar(): void
    {
        if (empty($this->itens)) {
            throw new InvalidArgumentException('Pedido deve ter ao menos um item');
        }
    }
}
```

---

## Herança

Herança (`extends`) permite que uma classe filha reutilize e estenda propriedades e métodos da classe pai:

```php
<?php

class Veiculo
{
    public function __construct(
        protected string $marca,
        protected string $modelo,
        protected int $ano,
    ) {}

    public function getDescricao(): string
    {
        return "{$this->marca} {$this->modelo} ({$this->ano})";
    }

    public function ligar(): string
    {
        return 'Veiculo ligado';
    }
}

class Carro extends Veiculo
{
    public function __construct(
        string $marca,
        string $modelo,
        int $ano,
        private int $portas = 4,
    ) {
        parent::__construct($marca, $modelo, $ano);
    }

    #[\Override]
    public function ligar(): string
    {
        return 'Carro ligado — vrum vrum!';
    }

    public function getInfoCompleta(): string
    {
        return parent::getDescricao() . " — {$this->portas} portas";
    }
}

class Moto extends Veiculo
{
    #[\Override]
    public function ligar(): string
    {
        return 'Moto ligada — randandandan!';
    }
}

$carro = new Carro('Toyota', 'Corolla', 2026, 4);
echo $carro->getInfoCompleta() . PHP_EOL; // Toyota Corolla (2026) — 4 portas
echo $carro->ligar() . PHP_EOL;           // Carro ligado — vrum vrum!

$moto = new Moto('Honda', 'CB500', 2025);
echo $moto->ligar();                       // Moto ligada — randandandan!
```

### `parent::`

A palavra-chave `parent::` acessa métodos e propriedades da classe pai:

```php
<?php

class LoggerBase
{
    protected function formatar(string $mensagem): string
    {
        return date('[Y-m-d H:i:s] ') . $mensagem;
    }
}

class LoggerArquivo extends LoggerBase
{
    #[\Override]
    protected function formatar(string $mensagem): string
    {
        return parent::formatar($mensagem) . ' [ARQUIVO]';
    }

    public function log(string $msg): void
    {
        echo $this->formatar($msg) . PHP_EOL;
    }
}

$logger = new LoggerArquivo();
$logger->log('Sistema iniciado');
// [2026-08-04 10:30:00] Sistema iniciado [ARQUIVO]
```

---

## Interfaces

Interfaces definem **contratos** — especificam quais métodos uma classe deve implementar, sem fornecer a implementação:

```php
<?php

interface Logavel
{
    public function getLogMessage(): string;
    public function getLogLevel(): string;
}

interface JsonSerializableCustom
{
    public function toJson(): string;
}

class Evento implements Logavel, JsonSerializableCustom
{
    public function __construct(
        private string $nome,
        private array $dados,
    ) {}

    public function getLogMessage(): string
    {
        return "Evento: {$this->nome} — " . json_encode($this->dados);
    }

    public function getLogLevel(): string
    {
        return 'info';
    }

    public function toJson(): string
    {
        return json_encode([
            'evento' => $this->nome,
            'dados'  => $this->dados,
        ], JSON_UNESCAPED_UNICODE);
    }
}

function registrar(Logavel $item): void
{
    echo "[{$item->getLogLevel()}] {$item->getLogMessage()}" . PHP_EOL;
}

$evento = new Evento('usuario.login', ['user_id' => 42, 'ip' => '192.168.1.1']);
registrar($evento); // [info] Evento: usuario.login — {"user_id":42,"ip":"192.168.1.1"}
echo $evento->toJson();
```

### Interface com Constantes (PHP 8.1+)

```php
<?php

interface Taxas
{
    public const float ICMS   = 0.18;
    public const float ISS    = 0.05;
    public const float PIS    = 0.0165;
    public const float COFINS = 0.076;
}

class NotaFiscal implements Taxas
{
    public function calcularImpostos(float $valor): float
    {
        return $valor * (Taxas::ICMS + Taxas::ISS + Taxas::PIS + Taxas::COFINS);
    }
}

$nf = new NotaFiscal();
echo "Impostos: R$ " . $nf->calcularImpostos(1000);
// Impostos: R$ 322.5
```

### Herança de Interfaces

```php
<?php

interface Contratual
{
    public function assinar(): void;
}

interface Renovavel extends Contratual
{
    public function renovar(): void;
}

class ContratoServico implements Renovavel
{
    public function assinar(): void
    {
        echo "Contrato assinado.\n";
    }

    public function renovar(): void
    {
        echo "Contrato renovado.\n";
    }
}
```

---

## Traits

Traits são mecanismos de **reutilização horizontal** de código. Permitem compartilhar métodos entre classes sem herança:

```php
<?php

trait Timestamps
{
    private DateTimeImmutable $criadoEm;
    private ?DateTimeImmutable $atualizadoEm = null;

    private function initTimestamps(): void
    {
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function touch(): void
    {
        $this->atualizadoEm = new DateTimeImmutable();
    }
}

trait HasUuid
{
    private string $uuid;

    private function initUuid(): void
    {
        $this->uuid = bin2hex(random_bytes(16));
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}

class Post
{
    use Timestamps, HasUuid;

    public function __construct(
        private string $titulo,
        private string $conteudo,
    ) {
        $this->initTimestamps();
        $this->initUuid();
    }

    public function editar(string $conteudo): void
    {
        $this->conteudo = $conteudo;
        $this->touch();
    }
}

$post = new Post('PHP 8.5', 'Novas features...');
echo $post->getUuid() . PHP_EOL;          // ex: a1b2c3d4...
echo $post->getCriadoEm()->format('c');    // 2026-08-04T10:30:00+00:00
```

### Resolução de Conflitos em Traits

Se duas traits definem o mesmo método, use `insteadof`:

```php
<?php

trait LoggerJson
{
    public function formatarLog(string $msg): string
    {
        return json_encode(['mensagem' => $msg]);
    }
}

trait LoggerTexto
{
    public function formatarLog(string $msg): string
    {
        return "[LOG] {$msg}";
    }
}

class MeuLogger
{
    use LoggerJson, LoggerTexto {
        LoggerJson::formatarLog insteadof LoggerTexto;  // usa o da LoggerJson
        LoggerTexto::formatarLog as formatarLogTexto;   // alias
    }
}

$logger = new MeuLogger();
echo $logger->formatarLog('teste');      // {"mensagem":"teste"}
echo $logger->formatarLogTexto('teste');  // [LOG] teste
```

### Traits com Métodos Abstratos

```php
<?php

trait Nomeavel
{
    abstract public function getNome(): string;

    public function getNomeExibicao(): string
    {
        return mb_strtoupper($this->getNome());
    }
}

class Categoria
{
    use Nomeavel;

    public function __construct(private string $nome) {}

    public function getNome(): string
    {
        return $this->nome;
    }
}

echo (new Categoria('eletronicos'))->getNomeExibicao(); // ELETRONICOS
```

---

## Classes Abstratas

Classes abstratas (`abstract`) não podem ser instanciadas. Servem como base para outras classes, podendo conter métodos abstratos e implementados:

```php
<?php

abstract class Funcionario
{
    public function __construct(
        protected string $nome,
        protected float $salarioBase,
    ) {}

    abstract public function calcularBonus(): float;

    public function getSalarioTotal(): float
    {
        return $this->salarioBase + $this->calcularBonus();
    }

    public function getNome(): string
    {
        return $this->nome;
    }
}

class Desenvolvedor extends Funcionario
{
    public function calcularBonus(): float
    {
        return $this->salarioBase * 0.2; // 20%
    }
}

class Gerente extends Funcionario
{
    public function calcularBonus(): float
    {
        return $this->salarioBase * 0.5; // 50%
    }
}

$dev = new Desenvolvedor('Joao', 10000);
$ger = new Gerente('Maria', 15000);

echo "{$dev->getNome()}: R$ {$dev->getSalarioTotal()}" . PHP_EOL; // Joao: R$ 12000
echo "{$ger->getNome()}: R$ {$ger->getSalarioTotal()}" . PHP_EOL; // Maria: R$ 22500

// $f = new Funcionario('Teste', 1000); // Erro: classe abstrata
```

---

## Propriedades e Métodos Estáticos

Membros `static` pertencem à **classe**, não à instância. São acessados com `Classe::membro` ou `self::` / `static::` / `parent::`:

```php
<?php

class Contador
{
    private static int $total = 0;

    public function __construct()
    {
        self::$total++;
    }

    public static function getTotal(): int
    {
        return self::$total;
    }

    public static function resetar(): void
    {
        self::$total = 0;
    }
}

new Contador();
new Contador();
new Contador();

echo Contador::getTotal(); // 3

Contador::resetar();
echo Contador::getTotal(); // 0
```

### `self::` vs `static::`

- `self::` — resolve na **classe onde está definido** (early binding)
- `static::` — resolve na **classe chamadora** (late static binding, LSB)

```php
<?php

class Pai
{
    public static function quem(): string
    {
        return self::class;
    }

    public static function quemReal(): string
    {
        return static::class;
    }
}

class Filha extends Pai {}

echo Filha::quem();      // Pai   (self:: bind no compile time)
echo Filha::quemReal();  // Filha (static:: bind no runtime)
```

---

## Constantes de Classe

Constantes de classe são declaradas com `const` e acessadas via `Classe::CONSTANTE` ou `self::` / `static::`:

```php
<?php

class StatusPedido
{
    public const string PENDENTE     = 'pendente';
    public const string PROCESSANDO  = 'processando';
    public const string ENVIADO      = 'enviado';
    public const string ENTREGUE     = 'entregue';
    public const string CANCELADO    = 'cancelado';

    private const array STATUS_FINAIS = [
        self::ENTREGUE,
        self::CANCELADO,
    ];

    public static function isFinal(string $status): bool
    {
        return in_array($status, self::STATUS_FINAIS, true);
    }
}

$status = StatusPedido::PROCESSANDO;
var_dump(StatusPedido::isFinal($status)); // bool(false)
var_dump(StatusPedido::isFinal(StatusPedido::ENTREGUE)); // bool(true)
```

### `final const` (PHP 8.1+)

Constantes `final` não podem ser sobrescritas em subclasses:

```php
<?php

class ConfigBase
{
    final public const string VERSAO = '1.0.0';
    public const string AMBIENTE = 'dev';
}

class ConfigProducao extends ConfigBase
{
    // public const string VERSAO = '2.0.0'; // Erro: nao pode sobrescrever final const
    public const string AMBIENTE = 'producao'; // OK
}
```

---

## Readonly: Propriedades e Classes

### Readonly Properties (PHP 8.1+)

Propriedades `readonly` só podem ser inicializadas **uma vez**, após o que se tornam imutáveis:

```php
<?php

class Cliente
{
    public function __construct(
        public readonly string $cpf,
        public readonly string $nome,
        public readonly DateTimeImmutable $dataCadastro = new DateTimeImmutable(),
    ) {}

    public function getAno(): int
    {
        return (int) $this->dataCadastro->format('Y');
    }
}

$c = new Cliente('123.456.789-00', 'Maria Silva');
echo $c->nome;      // Maria Silva
echo $c->getAno();  // 2026

// $c->nome = 'Outra'; // Erro: propriedade readonly
```

Propriedades `readonly` podem ser inicializadas **apenas** no constructor ou na declaração. Após isso, tornam-se imutáveis.

### Readonly Classes (PHP 8.2+)

Se **todas** as propriedades de uma classe forem `readonly`, você pode marcar a classe inteira como `readonly`:

```php
<?php

readonly class Endereco
{
    public function __construct(
        public string $rua,
        public string $cidade,
        public string $uf,
        public string $cep,
    ) {}
}

$end = new Endereco('Av. Paulista', 'Sao Paulo', 'SP', '01310-100');
echo "{$end->rua}, {$end->cidade} - {$end->uf}, {$end->cep}";
// Av. Paulista, Sao Paulo - SP, 01310-100

// $end->cidade = 'Rio'; // Erro: classe readonly
```

Classes `readonly`:
- Implicitamente tornam **todas** as propriedades tipadas como `readonly`
- Não podem ter propriedades `static`
- Não podem ter propriedades não tipadas
- Herança: uma classe `readonly` só pode ser herdada por outra classe `readonly`

⚠️ **Cuidado:** Propriedades `readonly` em classes normais não podem ser modificadas nem mesmo internamente após a inicialização. Classes `readonly` estendem essa restrição a todas as propriedades.

---

## Clonagem de Objetos

### `clone` — Cópia Superficial

`clone` cria uma cópia **superficial** (shallow copy) do objeto. Se houver propriedades que são referências a outros objetos, elas apontarão para o mesmo objeto:

```php
<?php

class Item
{
    public function __construct(public string $nome) {}
}

class Carrinho
{
    public function __construct(
        public array $itens = [],
        public DateTimeImmutable $criadoEm = new DateTimeImmutable(),
    ) {}
}

$c1 = new Carrinho(itens: [new Item('Notebook')]);
$c2 = clone $c1;

$c2->itens[0]->nome = 'Mouse';

echo $c1->itens[0]->nome; // Mouse — modificado! (shallow copy)
```

### `__clone()` — Controle sobre a Clonagem

O método mágico `__clone()` é chamado **após** a cópia superficial, permitindo fazer deep copy ou ajustes:

```php
<?php

class Documento
{
    public function __construct(
        public string $titulo,
        public DateTimeImmutable $criadoEm,
        public ?self $relacionado = null,
    ) {}

    public function __clone(): void
    {
        // Atualiza a data no clone
        $this->criadoEm = new DateTimeImmutable();

        // Deep clone do objeto relacionado
        if ($this->relacionado !== null) {
            $this->relacionado = clone $this->relacionado;
        }
    }
}

$original = new Documento('Original', new DateTimeImmutable('2026-01-01'));
$copia = clone $original;

echo $original->criadoEm->format('Y-m-d'); // 2026-01-01
echo $copia->criadoEm->format('Y-m-d');    // 2026-08-04 (data atual)
```

### Clone com Array de Propriedades — PHP 8.5 NOVIDADE!

**PHP 8.5+** — O operador `clone` agora aceita um array associativo para **alterar propriedades durante a clonagem**. Isso elimina a necessidade de setters intermediários ou métodos `with*` apenas para ajustar um clone:

```php
<?php

class Produto
{
    public function __construct(
        public readonly string $nome,
        public readonly float $preco,
        public readonly int $estoque = 0,
    ) {}
}

$produto = new Produto('Notebook', 3500.00, 10);

// PHP 8.5+: clone com sobrescrita de propriedades
$produtoComDesconto = clone $produto with ['preco' => 2999.00];
$produtoSemEstoque  = clone $produto with ['estoque' => 0];

echo $produto->preco;                    // 3500 (original inalterado)
echo $produtoComDesconto->preco;         // 2999
echo $produtoSemEstoque->estoque;        // 0

// Clone com multiplas propriedades:
$novoProduto = clone $produto with [
    'nome'    => 'Notebook Pro',
    'preco'   => 4500.00,
    'estoque' => 20,
];
```

Isso funciona bem com propriedades `readonly`, onde você **não poderia** reatribuir após a criação. A sintaxe `clone ... with` resolve este problema de forma elegante e segura, pois não modifica o objeto original:

```php
<?php

readonly class Configuracao
{
    public function __construct(
        public string $host,
        public int $porta,
        public bool $debug = false,
    ) {}
}

$dev = new Configuracao('localhost', 3306, debug: true);
$prod = clone $dev with ['host' => 'db.producao', 'debug' => false];

echo $dev->debug;  // true (inalterado)
echo $prod->debug; // false
```

💡 **Dica:** `clone ... with` substitui o padrão "wither" (métodos `withXxx()`) comum em objetos imutáveis. Em vez de escrever `withPreco()`, `withEstoque()`, etc., use `clone $obj with ['preco' => X]`.

---

## Property Hooks (PHP 8.4+)

**PHP 8.4+** — Property hooks permitem definir `get` e/ou `set` personalizados em propriedades, substituindo o comportamento padrão de leitura e escrita:

### Hook `get`

```php
<?php

class Usuario
{
    public string $nomeCompleto {
        get => mb_convert_case($this->nomeCompleto, MB_CASE_TITLE, 'UTF-8');
    }

    public function __construct(string $nome)
    {
        $this->nomeCompleto = $nome; // armazena o valor bruto; get transforma na leitura
    }
}

$u = new Usuario('joao da silva');
echo $u->nomeCompleto; // Joao Da Silva
```

### Hook `set`

```php
<?php

class Produto
{
    private float $precoBruto;

    public float $preco {
        get => $this->precoBruto;
        set (float $valor) {
            if ($valor < 0) {
                throw new InvalidArgumentException('Preco nao pode ser negativo');
            }
            $this->precoBruto = round($valor, 2);
        }
    }

    public function __construct(float $preco)
    {
        $this->preco = $preco;
    }
}

$p = new Produto(99.999);
echo $p->preco; // 100

// $p->preco = -10; // InvalidArgumentException
```

### Propriedade Somente Leitura (read-only virtual property)

```php
<?php

class Retangulo
{
    public function __construct(
        private float $largura,
        private float $altura,
    ) {}

    public float $area {
        get => $this->largura * $this->altura;
    } // sem set = somente leitura
}

$r = new Retangulo(10, 5);
echo $r->area; // 50

// $r->area = 60; // Erro: propriedade nao tem set hook
```

### Propriedade Somente Escrita

```php
<?php

class Logger
{
    private array $mensagens = [];

    public string $mensagem {
        set (string $valor) {
            $this->mensagens[] = date('[H:i:s] ') . $valor;
        }
    } // sem get = somente escrita

    public function getMensagens(): array
    {
        return $this->mensagens;
    }
}

$logger = new Logger();
$logger->mensagem = 'Sistema iniciado';
$logger->mensagem = 'Processamento concluido';

print_r($logger->getMensagens());
// [[10:30:00] Sistema iniciado, [10:30:05] Processamento concluido]

// echo $logger->mensagem; // Erro: propriedade nao tem get hook
```

### Property Hooks em Interfaces

```php
<?php

interface Nomeavel
{
    public string $nomeCompleto { get; }
}

class Pessoa implements Nomeavel
{
    public string $nomeCompleto {
        get => $this->primeiroNome . ' ' . $this->sobrenome;
    }

    public function __construct(
        public string $primeiroNome,
        public string $sobrenome,
    ) {}
}
```

💡 **Dica:** Property hooks substituem de forma limpa o que antes exigia métodos mágicos `__get()`/`__set()` ou propriedades privadas com getters/setters. Use-os para validação, transformação e propriedades computadas.

---

## Asymmetric Visibility (PHP 8.4+)

**PHP 8.4+** — Permite definir visibilidades **diferentes** para leitura (`get`) e escrita (`set`) de uma propriedade:

```php
<?php

class Relatorio
{
    // Todos leem, mas apenas a classe modifica
    public private(set) string $titulo;

    // Todos leem, classe e subclasses modificam
    public protected(set) int $visualizacoes = 0;

    public function __construct(string $titulo)
    {
        $this->titulo = $titulo;
    }

    public function incrementarVisualizacao(): void
    {
        $this->visualizacoes++;
    }
}

class RelatorioPremium extends Relatorio
{
    public function resetarVisualizacoes(): void
    {
        $this->visualizacoes = 0; // OK — protected(set)
        // $this->titulo = 'novo';     // Erro — private(set) nao acessivel em subclasse
    }
}

$rel = new Relatorio('Vendas Q3');
echo $rel->titulo;              // Vendas Q3 — public get

// $rel->titulo = 'Q4';          // Erro — private(set)
// $rel->visualizacoes = 100;    // Erro — protected(set)
```

### Sintaxes Suportadas

| Declaração | Leitura | Escrita |
|------------|---------|---------|
| `public private(set) string $x` | pública | privada (só a classe) |
| `public protected(set) string $x` | pública | protegida (classe + subclasses) |
| `protected private(set) string $x` | protegida | privada (só a classe) |
| `protected protected(set) string $x` | protegida | protegida (equivalente a `protected`) |

### Casos de Uso

```php
<?php

// Exemplo 1: UUID gerado uma vez, visivel externamente
class Entidade
{
    public private(set) string $id;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(16));
    }
}

// Exemplo 2: Contador somente incrementavel internamente
class Visitante
{
    public protected(set) int $acessos = 0;

    public function registrarAcesso(): void
    {
        $this->acessos++;
    }
}

// Exemplo 3: Configuracao que so pode ser alterada via metodo
class Conexao
{
    public private(set) string $host;
    public private(set) int $porta;

    public function __construct(string $host, int $porta)
    {
        $this->host  = $host;
        $this->porta = $porta;
    }

    public function reconectar(string $host, int $porta): void
    {
        $this->fechar();
        $this->host  = $host;
        $this->porta = $porta;
    }

    private function fechar(): void { /* ... */ }
}
```

💡 **Dica:** Asymmetric visibility reduz boilerplate — você não precisa mais de propriedades `private` + métodos `get()` públicos só para expor um valor sem permitir escrita externa.

---

## Final Property com Constructor Promotion — PHP 8.5 NOVIDADE!

**PHP 8.5+** — Agora é possível combinar `final` com constructor promotion, criando propriedades que **não podem ser sobrescritas em subclasses**:

```php
<?php

class Modelo
{
    public function __construct(
        final public string $tabela,
    ) {}
}

class Usuario extends Modelo
{
    // public string $tabela = 'usuarios'; // Erro: propriedade final nao pode ser sobrescrita
}
```

Também funciona com `readonly`:

```php
<?php

abstract class EventoDominio
{
    public function __construct(
        final public readonly string $id,
        final public readonly DateTimeImmutable $ocorridoEm = new DateTimeImmutable(),
    ) {}
}

class UsuarioCriado extends EventoDominio
{
    public function __construct(
        string $id,
        public readonly string $nome,
    ) {
        parent::__construct($id);
    }

    // Nao pode sobrescrever $id nem $ocorridoEm
}
```

---

## Métodos Mágicos

Métodos mágicos são interceptores chamados pelo PHP em situações específicas. Todos começam com `__`:

### `__get($nome)` — Acesso a Propriedade Inacessível

Chamado ao tentar **ler** uma propriedade que não existe ou é inacessível:

```php
<?php

class Container
{
    private array $dados = [];

    public function __get(string $nome): mixed
    {
        return $this->dados[$nome] ?? null;
    }
}

$c = new Container();
echo $c->qualquer_chave; // null (sem erro!)
```

### `__set($nome, $valor)` — Escrita em Propriedade Inacessível

```php
<?php

class ConfigDinamica
{
    private array $valores = [];

    public function __set(string $nome, mixed $valor): void
    {
        $this->valores[$nome] = $valor;
    }

    public function __get(string $nome): mixed
    {
        return $this->valores[$nome] ?? null;
    }

    public function getAll(): array
    {
        return $this->valores;
    }
}

$config = new ConfigDinamica();
$config->debug = true;
$config->host  = 'localhost';
$config->porta = 3306;

print_r($config->getAll());
/*
Array
(
    [debug] => 1
    [host] => localhost
    [porta] => 3306
)
*/
```

### `__call($metodo, $args)` — Chamada a Método Inacessível

```php
<?php

class MicroOrm
{
    public function __call(string $metodo, array $args): mixed
    {
        if (str_starts_with($metodo, 'findBy')) {
            $coluna = lcfirst(substr($metodo, 6));
            return $this->findBy($coluna, $args[0]);
        }

        throw new BadMethodCallException("Metodo {$metodo} nao existe");
    }

    private function findBy(string $coluna, mixed $valor): ?array
    {
        // simulacao de consulta
        echo "SELECT * FROM tabela WHERE {$coluna} = {$valor}\n";
        return null;
    }
}

$orm = new MicroOrm();
$orm->findByNome('Maria');   // SELECT * FROM tabela WHERE nome = Maria
$orm->findByEmail('a@b.com'); // SELECT * FROM tabela WHERE email = a@b.com
```

### `__toString()` — Representação como String

```php
<?php

class Dinheiro
{
    public function __construct(
        private float $valor,
        private string $moeda = 'BRL',
    ) {}

    public function __toString(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }
}

$preco = new Dinheiro(199.9);
echo $preco;              // R$ 199,90
echo "Preco: {$preco}";  // Preco: R$ 199,90
```

### `__invoke()` — Objeto como Função

Faz com que um objeto possa ser chamado como se fosse uma função:

```php
<?php

class ValidadorEmail
{
    public function __invoke(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

$validador = new ValidadorEmail();

var_dump($validador('user@domain.com')); // bool(true)
var_dump($validador('invalid'));         // bool(false)

// Uso como callback:
$emails = ['a@b.com', 'invalido', 'c@d.com'];
$validos = array_filter($emails, $validador);
print_r($validos); // ['a@b.com', 'c@d.com']
```

### `__debugInfo()` — Controlar Saída do `var_dump()`

```php
<?php

class Usuario
{
    public function __construct(
        private string $nome,
        private string $senha,
        private string $email,
    ) {}

    public function __debugInfo(): array
    {
        return [
            'nome'  => $this->nome,
            'email' => $this->email,
            'senha' => '***REDACTED***',
        ];
    }
}

$u = new Usuario('Maria', 'senha123', 'maria@email.com');
var_dump($u);
/*
object(Usuario)#1 (3) {
  ["nome"]=> string(5) "Maria"
  ["email"]=> string(16) "maria@email.com"
  ["senha"]=> string(13) "***REDACTED***"
}
*/
```

💡 **Dica:** Use `__debugInfo()` para ocultar dados sensíveis (senhas, tokens) ao fazer debugging.

---

## Late Static Binding

Late Static Binding (`static::`) resolve a referência na **classe que fez a chamada** (runtime), não na classe onde o código está escrito (compile time). Essencial para hierarquias com métodos de fábrica:

```php
<?php

abstract class Repository
{
    protected static string $tabela;

    public static function find(int $id): ?static
    {
        $tabela = static::$tabela;  // LSB: resolve na subclasse
        echo "SELECT * FROM {$tabela} WHERE id = {$id}" . PHP_EOL;
        return $id > 0 ? new static() : null; // LSB: instancia a subclasse
    }

    public static function tabela(): string
    {
        return static::$tabela;
    }
}

class UsuarioRepository extends Repository
{
    protected static string $tabela = 'usuarios';
}

class PedidoRepository extends Repository
{
    protected static string $tabela = 'pedidos';
}

$user = UsuarioRepository::find(1);
// SELECT * FROM usuarios WHERE id = 1

$pedido = PedidoRepository::find(42);
// SELECT * FROM pedidos WHERE id = 42

echo UsuarioRepository::tabela(); // usuarios
echo PedidoRepository::tabela();  // pedidos
```

Sem `static::`, `self::$tabela` sempre retornaria o valor da classe `Repository` (que não está definido).

---

## Autoloading Básico

Autoloading carrega classes quando são referenciadas pela primeira vez, eliminando a necessidade de `require`/`include` manual.

### `spl_autoload_register()`

```php
<?php

// Configuracao basica de autoloading
spl_autoload_register(function (string $classe): void {
    // Converte namespace em caminho de arquivo
    // Ex: App\Models\Usuario -> src/Models/Usuario.php
    $arquivo = __DIR__ . '/src/' . str_replace('\\', '/', $classe) . '.php';

    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});

// Agora qualquer classe no namespace App sera carregada:
// use App\Models\Usuario;
// use App\Services\EmailService;
```

### Padrão PSR-4 (Recomendado)

Use o Composer para autoloading PSR-4. Arquivo `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Após rodar `composer dump-autoload`, todas as classes no diretório `src/` com namespace `App\` são carregadas. O Composer gera um mapa otimizado em `vendor/autoload.php`.

```php
<?php

// No entry point da aplicacao:
require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\HomeController;
use App\Models\Usuario;

$controller = new HomeController();  // carregado
$usuario     = new Usuario();        // carregado
```

---

## 📚 Referências

- [Documentação oficial: Classes e Objetos](https://www.php.net/manual/pt_BR/language.oop5.php)
- [Visibilidade](https://www.php.net/manual/pt_BR/language.oop5.visibility.php)
- [Construtores e Destrutores](https://www.php.net/manual/pt_BR/language.oop5.decon.php)
- [Constructor Promotion (PHP 8.0)](https://www.php.net/manual/pt_BR/language.oop5.decon.php#language.oop5.decon.constructor.promotion)
- [Herança](https://www.php.net/manual/pt_BR/language.oop5.inheritance.php)
- [Interfaces](https://www.php.net/manual/pt_BR/language.oop5.interfaces.php)
- [Traits](https://www.php.net/manual/pt_BR/language.oop5.traits.php)
- [Classes Abstratas](https://www.php.net/manual/pt_BR/language.oop5.abstract.php)
- [Métodos e Propriedades Estáticas](https://www.php.net/manual/pt_BR/language.oop5.static.php)
- [Constantes de Classe](https://www.php.net/manual/pt_BR/language.oop5.constants.php)
- [Readonly Properties (PHP 8.1)](https://www.php.net/manual/pt_BR/language.oop5.properties.php#language.oop5.properties.readonly-properties)
- [Readonly Classes (PHP 8.2)](https://www.php.net/manual/pt_BR/language.oop5.basic.php#language.oop5.basic.class.readonly)
- [Clonagem de Objetos](https://www.php.net/manual/pt_BR/language.oop5.cloning.php)
- [Clone with — PHP 8.5](https://wiki.php.net/rfc/clone_with)
- [Property Hooks (PHP 8.4)](https://www.php.net/manual/pt_BR/language.oop5.property-hooks.php)
- [Asymmetric Visibility (PHP 8.4)](https://www.php.net/manual/pt_BR/language.oop5.visibility.php#language.oop5.visibility.asymmetric)
- [Final Property com Constructor Promotion — PHP 8.5](https://wiki.php.net/rfc/final_properties)
- [Métodos Mágicos](https://www.php.net/manual/pt_BR/language.oop5.magic.php)
- [Late Static Binding](https://www.php.net/manual/pt_BR/language.oop5.late-static-bindings.php)
- [Autoloading com spl_autoload_register](https://www.php.net/manual/pt_BR/function.spl-autoload-register.php)
- [PSR-4: Autoloading Standard](https://www.php-fig.org/psr/psr-4/)
- [Atributo #[\Override]](https://www.php.net/manual/pt_BR/language.attributes.php#language.attributes.override)

---

> **Capítulo anterior:** [08 — Strings](08-strings.md)
> **Próximo capítulo:** [10 — Tratamento de Erros e Exceções](10-tratamento-de-erros.md)
