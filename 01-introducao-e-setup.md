# 01 — Introdução e Setup

## O que é PHP?

PHP (antes **Personal Home Page**, hoje **PHP: Hypertext Preprocessor**) é uma linguagem de script **server-side** de código aberto, criada por Rasmus Lerdorf em 1994. Ela é embutida direto no HTML e executada no servidor antes de enviar a resposta ao navegador.

Segundo a W3Techs, **cerca de 79% de todos os sites cujo lado servidor é conhecido utilizam PHP** — isso inclui gigantes como WordPress, Facebook (no início), Wikipedia, Slack, Mailchimp e Etsy.

### Marcos importantes

| Ano     | Versão       | Destaque                                              |
|---------|--------------|-------------------------------------------------------|
| 1995    | PHP 1.0      | Primeira versão pública (PHP/FI)                      |
| 1997    | PHP 3.0      | Reescreveram o motor; nasce o nome PHP                |
| 2000    | PHP 4.0      | Motor Zend Engine                                     |
| 2004    | PHP 5.0      | OOP completo, PDO                                     |
| 2015    | PHP 7.0      | Performance 2x maior, scalar type hints               |
| 2020    | PHP 8.0      | JIT, union types, match, named arguments, nullsafe     |
| 2023    | PHP 8.3      | json_validate, override attr, readonly em clone       |
| 2024    | PHP 8.4      | Property hooks, asymmetric visibility, array_find     |
| **2025** | **PHP 8.5** | **Operador pipe `\|>`, PdoStubs, melhorias stdClass** |

> 💡 **Dica**: PHP 8.4 e 8.5 são as versões mantidas em agosto de 2026.
> Versões anteriores (8.3 e abaixo) já não recebem mais atualizações de segurança.
> Confira em: [php.net/supported-versions](https://www.php.net/supported-versions.php)

---

## Como o PHP funciona?

O PHP é uma linguagem **interpretada no servidor**. O fluxo básico de uma requisição é:

```
Navegador (cliente) → Requisição HTTP → Servidor Web (Apache/Nginx)
                                              ↓
                                    Interpretador PHP processa o script
                                              ↓
                                    Gera HTML (ou JSON, XML, etc.)
                                              ↓
                              Resposta HTTP → Navegador (cliente)
```

Diferente do JavaScript tradicional que roda no navegador, o código PHP **nunca chega ao cliente**. O navegador recebe apenas o **resultado** da execução (HTML puro).

### Exemplo mínimo de arquivo PHP

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minha página PHP</title>
</head>
<body>
    <h1>Olá, mundo!</h1>
    <p>A data de hoje é: <?php echo date('d/m/Y'); ?></p>
</body>
</html>
```

O trecho `<?php echo date('d/m/Y'); ?>` é processado pelo servidor e substituído pela data real. O navegador nunca vê `<?php ... ?>`.

---

## Instalação do PHP com asdf

[asdf](https://asdf-vm.com/) gerencia múltiplas versões de PHP (e outras linguagens) sem conflitos.

```bash
# Adiciona o plugin de PHP
asdf plugin add php https://github.com/asdf-community/asdf-php.git

# Dependências de compilação (Ubuntu/Debian)
sudo apt install autoconf bison build-essential curl gettext re2c \
  libxml2-dev libsqlite3-dev libcurl4-openssl-dev libssl-dev libonig-dev \
  libzip-dev libbz2-dev libreadline-dev libpng-dev libjpeg-dev libwebp-dev \
  libgd-dev libpq-dev

# Instala o PHP 8.5
asdf install php 8.5.0

# Define como global (ou asdf local dentro de um projeto)
asdf global php 8.5.0

php -v
```

Para trocar de versão: `asdf local php 8.4.0` na raiz do projeto. O asdf cuida do resto.

### Verificando a instalação

```php
<?php
phpinfo();
```

`phpinfo()` exibe todas as configurações, extensões carregadas, versão e ambiente.

---

## Ferramentas essenciais

### Servidor embutido (`php -S`)

O PHP vem com um servidor web **embutido**, ideal para desenvolvimento local:

```bash
# Inicia o servidor na porta 8000, servindo o diretório atual
php -S localhost:8000

# Especificando um diretório raiz
php -S localhost:8080 -t ./public

# Com um arquivo "router" para URLs amigáveis
php -S localhost:8000 router.php
```

Acesse `http://localhost:8000` no navegador.

> ⚠️ **Cuidado**: O servidor embutido é **apenas para desenvolvimento**.
> Nunca use em produção. Ele é single-threaded e não tem recursos de segurança.

### Modo interativo (`php -a`)

```bash
php -a
```

Dentro do modo interativo você pode digitar código PHP linha a linha e ver o resultado já, similar ao console do Python ou Node.js.

```php
Interactive shell

php > $name = "Maria";
php > echo "Olá, $name!";
Olá, Maria!
php > echo 2 + 2;
4
php > exit;
```

---

## Primeiro script PHP

Crie um arquivo chamado `hello.php`:

```php
<?php

declare(strict_types=1);

/*
 * Meu primeiro script PHP
 * Execução: php hello.php
 */

$name = $argv[1] ?? 'Mundo';

echo "Olá, {$name}!\n";
echo "PHP versão: " . PHP_VERSION . "\n";
echo "Sistema: " . PHP_OS . "\n";
```

Execute:

```bash
php hello.php
# Saída: Olá, Mundo!
#        PHP versão: 8.5.0
#        Sistema: Linux

php hello.php Maria
# Saída: Olá, Maria!
#        PHP versão: 8.5.0
#        Sistema: Linux
```

`$argv` é um array global que contém os argumentos passados pela linha de comando. `$argv[0]` é o nome do script, `$argv[1]` é o primeiro argumento.

---

## php.ini — Configuração básica

O `php.ini` é o arquivo de configuração do PHP. Para localizá-lo:

```bash
php --ini
# Exibe o caminho do php.ini carregado

php -i | grep "Loaded Configuration File"
```

Configurações essenciais para desenvolvimento:

```ini
; Exibe erros na tela (desenvolvimento)
display_errors = On
display_startup_errors = On

; Nível de relatório de erros: mostrar todos
error_reporting = E_ALL

; Tamanho máximo de upload
upload_max_filesize = 20M
post_max_size = 20M

; Limite de memória
memory_limit = 256M

; Timezone
date.timezone = America/Sao_Paulo

; Extensões (descomente conforme necessário)
extension=mbstring
extension=pdo_mysql
extension=curl
extension=fileinfo
```

> ⚠️ **Cuidado**: Em **produção**, `display_errors` deve ser `Off`.
> Erros devem ser logados, nunca exibidos ao usuário final.

---

## Estrutura de um projeto PHP

### Antes: o que são PSRs?

O [PHP-FIG](https://www.php-fig.org/) (PHP Framework Interop Group) é um grupo que define **PSRs** (PHP Standards Recommendations): padrões que a comunidade PHP adota para interoperabilidade entre bibliotecas e frameworks. Funciona como as PEPs do Python ou as especificações do ECMAScript.

Os PSRs que importam no dia a dia:

| PSR | Assunto | Resumo |
|---|---|---|
| **PSR-1** | Basic Coding Standard | PascalCase em classes, camelCase em métodos, UPPER_SNAKE em constantes |
| **PSR-4** | Autoloading | Mapeia namespace → diretório. `App\Models\User` ⇢ `src/Models/User.php` |
| **PSR-12** | Extended Coding Style | 4 espaços, chaves na mesma linha, visibilidade explícita, 120 colunas |

Sem PSR-4 você precisaria de `require` manual pra cada classe. Com ele, o Composer faz o autoload via `vendor/autoload.php`.

### Convenções de nomenclatura no ecossistema PHP

| O que | Padrão | Exemplo | Por quê |
|---|---|---|---|
| Classes, interfaces, traits | **PascalCase** | `UserController`, `ProductService`, `OrderRepository` | PSR-1 exige. Mapeia 1:1 com o nome do arquivo via PSR-4 |
| Métodos e propriedades | **camelCase** | `findById()`, `$this->createdAt`, `$totalPrice` | PSR-1/PHP-FIG. Consistente com Java, C#, JS |
| Funções PHP built-in | **snake_case** | `file_get_contents()`, `array_key_exists()` | Legado pré-PSR. Não mude — é a cara do PHP |
| Constantes | **UPPER_SNAKE_CASE** | `MAX_UPLOAD_SIZE`, `DEFAULT_LIMIT` | PSR-1 |
| Arquivos de classe | **PascalCase.php** | `UserController.php`, `Order.php` | PSR-4: `App\Models\Order` → `src/Models/Order.php` |
| Arquivos de config/template | **snake_case.php** | `database.php`, `error_handler.php` | Não são classes, então PascalCase não faz sentido |
| Pastas de namespace (src/) | **PascalCase** | `src/Models/`, `src/Controllers/`, `src/Services/` | PSR-4: cada pasta = um segmento do namespace |
| Pastas gerais | **lowercase** | `public/`, `config/`, `templates/`, `var/`, `vendor/` | Não mapeiam para namespaces de classe |
| Raiz do projeto | **kebab-case** | `my-ecommerce-api/`, `blog-engine/` | Segue convenção de pacotes Composer (`vendor/package-name`) |
| Tabelas SQL | **snake_case** (plural) | `users`, `order_items`, `password_resets` | Convenção SQL. Plural porque uma tabela contém múltiplos registros |
| Colunas SQL | **snake_case** | `created_at`, `updated_at`, `user_id`, `full_name` | Chave estrangeira: `{tabela}_id` |

### Por que src/Models/ começa com maiúscula e config/ não?

O PSR-4 resolve `App\Models\User` para `src/Models/User.php`. Cada segmento do namespace é uma pasta, e nomes de classe são PascalCase por definição. A estrutura de diretórios **espelha** o namespace:

```php
// composer.json define:
// "App\\" => "src/"

// Então:
use App\Models\User;        // → src/Models/User.php
use App\Controllers\Auth\LoginController;  // → src/Controllers/Auth/LoginController.php
use App\Services\Payment\StripeGateway;    // → src/Services/Payment/StripeGateway.php
```

Pastas como `config/`, `public/`, `templates/` não fazem parte do autoload — elas guardam arquivos de infraestrutura, não classes. Por isso usam lowercase.

### Estrutura típica com nomes compostos

```
my-ecommerce/
├── public/                    # Document root do servidor web
│   ├── index.php              # Front controller — toda requisição entra aqui
│   └── assets/                # CSS, JS, imagens (servidos diretamente)
│       ├── main.css
│       └── app.js
├── src/                       # Namespace App\
│   ├── Models/
│   │   ├── User.php           # App\Models\User
│   │   ├── Product.php        # App\Models\Product
│   │   └── Order.php          # App\Models\Order
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   └── Admin/
│   │       └── DashboardController.php
│   ├── Services/
│   │   ├── PaymentGateway.php
│   │   └── EmailSender.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   └── ProductRepository.php
│   └── Middleware/
│       ├── AuthMiddleware.php
│       └── CsrfMiddleware.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── mail.php
├── templates/                 # Views (HTML + PHP)
│   ├── layout.php
│   ├── home.php
│   └── admin/
│       ├── dashboard.php
│       └── login.php
├── var/                       # Arquivos gerados (cache, logs, uploads temporários)
│   ├── cache/
│   └── log/
├── vendor/                    # Composer — nunca edite manualmente
├── tests/
│   ├── Unit/
│   │   └── Models/
│   │       └── UserTest.php
│   └── Feature/
│       └── Controllers/
│           └── HomeControllerTest.php
├── composer.json
├── composer.lock
└── .gitignore
```

`.gitignore` mínimo:

```
/vendor/
/var/
.env
.phpunit.result.cache
```

## Ferramentas de qualidade de código

PHP tem seu próprio ecossistema de linting e formatação. Os equivalentes PHP ao que você conhece de outras linguagens:

| Ferramenta | Tipo | Equivalente JS | O que faz |
|---|---|---|---|
| **[PHP CS Fixer](https://cs.symfony.com/)** | Formatter | Prettier | Corrige código pra PSR-12 (espaços, chaves, imports). Roda `php-cs-fixer fix` e o arquivo fica no padrão |
| **[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)** | Linter | ESLint | Detecta violações de PSR-12 sem corrigir. `phpcs --standard=PSR12 src/` |
| **[PHPStan](https://phpstan.org/)** | Static analysis | TypeScript | Análise de tipos sem rodar o código. Encontra bugs como chamar método inexistente, tipo errado, null pointer |
| **[Pint](https://laravel.com/docs/pint)** | Formatter | Prettier (wrapper) | Wrapper do PHP CS Fixer com defaults Laravel. Zero config |

Na prática, a combinação comum é:

```bash
# Instala global (ou como dev dependency no composer.json)
composer global require friendsofphp/php-cs-fixer

# Formata um diretório inteiro no padrão PSR-12
php-cs-fixer fix src/ --rules=@PSR12

# Análise estática (nível progressivo, começa no 1)
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src/ --level=5
```

### Integração com PhpStorm

PhpStorm tem PHP CS Fixer e PHPStan built-in como inspections. Vai em **Settings → PHP → Quality Tools** e aponta pros binários. O PhpStorm formata em PSR-12 no `Ctrl+Alt+L`.

---

## Linha de comando PHP útil

```bash
# Versão do PHP
php -v

# Informações completas
php -i

# Verifica sintaxe de um arquivo sem executá-lo
php -l file.php

# Lista extensões carregadas
php -m

# Executa servidor embutido
php -S localhost:8000

# Modo interativo
php -a

# Executa código inline
php -r "echo PHP_VERSION;"

# Executa script com php.ini customizado
php -c /caminho/php.ini script.php

# Mostra todas as opções
php --help
```

- Para formatar código no padrão PSR-12: [PHP CS Fixer](https://cs.symfony.com/) ou [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

---

## Recursos de aprendizado

### 📚 Referências

- **Manual oficial em português**: [php.net/manual/pt_BR](https://www.php.net/manual/pt_BR/)
- **PHP The Right Way**: [phptherightway.com](https://phptherightway.com/) — guia atualizado de boas práticas
- **PHP.net/supported-versions**: [php.net/supported-versions](https://www.php.net/supported-versions.php) — versões mantidas
- **PHP-FIG / PSRs**: [php-fig.org/psr](https://www.php-fig.org/psr/)
- **Composer**: [getcomposer.org](https://getcomposer.org/)
- **PHP Watch**: [php.watch](https://php.watch/) — novidades de cada versão
- **Stitcher.io/blog**: [stitcher.io/blog](https://stitcher.io/blog) — artigos aprofundados sobre PHP
- **PHP Annotated Monthly**: [blog.jetbrains.com/phpstorm](https://blog.jetbrains.com/phpstorm/category/php-annotated-monthly/)
- **Laracasts PHP Path**: [laracasts.com](https://laracasts.com/) — vídeo-aulas (em inglês, algumas gratuitas)

### Comunidade brasileira

- **PHP Brasil (Telegram)**: t.me/phpbrasil
- **PHPSP (São Paulo)**: phpsp.org.br
- **PHP com Rapadura** (podcast)
- **Dev na Gringa — PHP**

---

## Próximo módulo

No próximo módulo: **Sintaxe Básica** — tags PHP, comentários, echo, include, constantes e namespaces.

[→ 02 — Sintaxe Básica](./02-sintaxe-basica.md)
