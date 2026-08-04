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

php > $nome = "Maria";
php > echo "Olá, $nome!";
Olá, Maria!
php > echo 2 + 2;
4
php > exit;
```

---

## Editores e IDEs recomendados

### Visual Studio Code (recomendado)

Baixe em: [code.visualstudio.com](https://code.visualstudio.com/)

Extensões essenciais:

| Extensão               | Função                                        |
|------------------------|-----------------------------------------------|
| **PHP Intelephense**   | IntelliSense, autocomplete, refatoração       |
| **PHP Debug**          | Depuração com Xdebug (breakpoints, step into) |
| **PHP Namespace Resolver** | Import automático de namespaces            |
| **PHP CS Fixer**       | Formatação automática PSR-12                  |
| **phpstan**            | Análise estática (via terminal)               |
| **Error Lens**         | Exibe erros inline no editor                  |

### PhpStorm (JetBrains)

IDE comercial completa. Melhor para projetos grandes. Tem trial de 30 dias: [jetbrains.com/phpstorm](https://www.jetbrains.com/phpstorm/)

---

## Primeiro script PHP

Crie um arquivo chamado `ola.php`:

```php
<?php

declare(strict_types=1);

/*
 * Meu primeiro script PHP
 * Execução: php ola.php
 */

$nome = $argv[1] ?? 'Mundo';

echo "Olá, {$nome}!\n";
echo "PHP versão: " . PHP_VERSION . "\n";
echo "Sistema: " . PHP_OS . "\n";
```

Execute:

```bash
php ola.php
# Saída: Olá, Mundo!
#        PHP versão: 8.5.0
#        Sistema: Linux

php ola.php Maria
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

## Estrutura de um projeto PHP simples

```
meu-projeto/
├── public/            # Ponto de entrada público (document root)
│   └── index.php      # Front controller
├── src/               # Código fonte da aplicação
│   ├── Models/
│   ├── Controllers/
│   └── Services/
├── config/            # Arquivos de configuração
│   ├── app.php
│   └── database.php
├── templates/         # Templates/views (HTML)
│   ├── layout.php
│   └── home.php
├── var/               # Arquivos temporários (cache, logs)
│   ├── cache/
│   └── log/
├── vendor/            # Dependências (gerado pelo Composer)
├── tests/             # Testes automatizados
├── composer.json      # Configuração do Composer
└── .gitignore
```

`.gitignore` recomendado:

```
/vendor/
/var/
.env
.phpunit.result.cache
```

---

## Composer — Gerenciador de dependências

O [Composer](https://getcomposer.org/) é o gerenciador de pacotes padrão do PHP. Ele resolve e instala dependências, faz autoload de classes e gerencia versões.

### Instalação (Linux/macOS)

```bash
# Baixa e instala
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# Verifica
composer --version
```

### Uso básico

```bash
# Inicia um novo projeto
composer init

# Instala uma dependência
composer require monolog/monolog

# Instala dependências de um projeto existente
composer install

# Atualiza dependências
composer update

# Autoload
composer dump-autoload
```

O arquivo `composer.json` define as dependências do projeto. O `composer.lock` fixa as versões exatas instaladas (deve ser commitado no Git).

Exemplo mínimo de `composer.json`:

```json
{
    "name": "meu-app/php",
    "description": "Meu projeto PHP",
    "type": "project",
    "require": {
        "php": ">=8.4"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

---

## Linha de comando PHP útil

```bash
# Versão do PHP
php -v

# Informações completas
php -i

# Verifica sintaxe de um arquivo sem executá-lo
php -l arquivo.php

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

---

## Convenções importantes (PSR-1 e PSR-12)

O PHP-FIG (PHP Framework Interop Group) define **PSRs** (PHP Standards Recommendations), que são padrões que a comunidade adota.

### PSR-1: Basic Coding Standard

- Arquivos **DEVEM** usar tags `<?php` (a tag curta `<?` não é recomendada)
- Arquivos só com PHP **NÃO DEVEM** ter a tag de fechamento `?>`
- Nomes de classes em **PascalCase**: `MinhaClasse`, `BancoDeDados`
- Constantes de classe em **UPPER_SNAKE_CASE**: `TAXA_JUROS`
- Nomes de métodos em **camelCase**: `calcularTotal()`

### PSR-12: Extended Coding Style

- Indentação com **4 espaços** (não tabs)
- Linhas com no máximo **120 caracteres** (de preferência 80)
- Chave de abertura `{` na **mesma linha** da declaração
- Uma linha em branco entre métodos
- `declare(strict_types=1);` seguido de linha em branco
- Visibilidade (`public`, `protected`, `private`) **sempre explícita**

Exemplo:

```php
<?php

declare(strict_types=1);

namespace App\Services;

class Calculadora
{
    private float $taxa;

    public function __construct(float $taxa)
    {
        $this->taxa = $taxa;
    }

    public function aplicarTaxa(float $valor): float
    {
        return $valor * (1 + $this->taxa);
    }
}
```

Para formatar código no padrão PSR-12, use o [PHP CS Fixer](https://cs.symfony.com/) ou o [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

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
