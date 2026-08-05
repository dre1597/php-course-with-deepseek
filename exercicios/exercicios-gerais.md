# Exercícios Gerais — Curso Completo de PHP

Este arquivo contém exercícios práticos que cobrem os 15 módulos do curso. Cada módulo tem de 3 a 5 exercícios progressivos (fácil → médio → difícil). Ao final, gabarito com dicas para cada exercício.

---

## Módulo 1–5: Fundamentos (Sintaxe, Variáveis, Estruturas, Funções, Arrays)

### Exercício 1.1 — Calculadora de IMC (Fácil)
Crie um script que receba `$peso` (kg) e `$altura` (m), calcule o IMC (`peso / altura²`) e exiba a classificação:
- Abaixo do peso: < 18.5
- Peso normal: 18.5 — 24.9
- Sobrepeso: 25 — 29.9
- Obesidade: >= 30

**Requisitos:** Use `if/elseif/else`. Arredonde o IMC com 1 casa decimal.

### Exercício 1.2 — Tabuada com For (Fácil)
Crie um script que gere a tabuada de um número `$n` de 1 a 10, exibindo no formato: `7 x 3 = 21`.

**Requisitos:** Use `for`. O número base deve ser uma variável que você possa alterar.

### Exercício 1.3 — Validator de CPF Simplificado (Médio)
Crie uma função que receba uma string com o CPF (apenas números ou formatado `XXX.XXX.XXX-XX`) e valide se os dígitos verificadores estão corretos.

**Requisitos:** Remova caracteres não numéricos. Implemente o algoritmo dos dois dígitos verificadores. Retorne `true` ou `false`.

### Exercício 1.4 — Gerador de Senhas (Médio)
Crie uma função que gere uma senha aleatória com tamanho configurável, contendo obrigatoriamente maiúsculas, minúsculas, números e caracteres especiais.

**Requisitos:** Use `random_int()`. Parametrize o tamanho (padrão: 16). A senha deve começar com uma letra.

### Exercício 1.5 — Analisador de Dados de Array (Difícil)
Dado um array associativo multidmensional de vendas, calcule e exiba:
- Total de vendas por vendedor
- Vendedor com maior valor total
- Mês com maior volume de vendas
- Ticket médio geral

```php
$vendas = [
    ['seller' => 'João',  'month' => 'Janeiro',  'amount' => 1500.00],
    ['seller' => 'Maria', 'month' => 'Janeiro',  'amount' => 2300.50],
    ['seller' => 'João',  'month' => 'Fevereiro','amount' => 1800.00],
    ['seller' => 'Pedro', 'month' => 'Janeiro',  'amount' => 1200.75],
    ['seller' => 'Maria', 'month' => 'Fevereiro','amount' => 3100.00
```

---

## Módulo 6–7: Strings e Expressões Regulares

### Exercício 2.1 — Slug de URL (Fácil)
Crie uma função que converta uma string em um slug de URL: minúsculas, sem acentos, espaços viram hífens, apenas letras, números e hífens.

```php
echo slug('Curso de PHP 8 — Avançado!'); // curso-de-php-8-ava
```

### Exercício 2.2 — Extrator de Emails (Médio)
Crie uma função que receba um texto e retorne um array com todos os endereços de email encontrados (regex).

### Exercício 2.3 — Template Engine Simplificada (Médio)
Crie uma função que receba uma string com placeholders `{{variavel}}` e um array associativo, e substitua os placeholders pelos valores correspondentes. Use regex.

```php
$template = 'Olá {{name}}, seu pedido #{{pedido}} está {{status}}.';
echo render($template, ['name' => 'João', 'pedido' => 1234, 'status' => 'entregu
```

### Exercício 2.4 — Formatador de Telefone (Fácil)
Crie uma função que receba um número de telefone (10 ou 11 dígitos, apenas números) e retorne formatado:
- 10 dígitos: `(11) 9999-8888`
- 11 dígitos: `(11) 99999-8888`

Se o número for inválido, lance uma exceção.

---

## Módulo 8–10: OOP, Tratamento de Erros e Namespaces

### Exercício 3.1 — Classe Conta Bancária (Fácil)
Crie a classe `ContaBancaria` com propriedades privadas: `$titular`, `$saldo`, `$numero`. Métodos: `depositar($value)`, `sacar($value)`, `getSaldo()`. Validações: não pode sacar mais que o saldo, não pode depositar/sacar valores negativos.

### Exercício 3.2 — Sistema Simples de Eventos (Médio)
Crie uma classe `EventDispatcher` que permita registrar listeners para eventos e dispará-los. Use closure/callable.

```php
$dispatcher = new EventDispatcher();
$dispatcher->on('user.created', function ($user) { echo "Usuário {$user} criado."; });
$dispatcher->dispatch('user.created', 'Jo
```

### Exercício 3.3 — Exceções Personalizadas (Médio)
Crie uma hierarquia de exceções: `AppException` (base), `ValidationException`, `DatabaseException`, `NotFoundException`. Demonstre o uso com `try/catch` aninhados e blocos `finally`.

### Exercício 3.4 — Autoload com Namespace (Difícil)
Implemente um sistema de autoload compatível com PSR-4 na mão. Defina namespaces como `App\Models\User` e `App\Controllers\HomeController`. O autoload deve buscar os arquivos em `src/` correspondente à hierarquia de namespace.

---

## Módulo 11: Manipulação de Arquivos

### Exercício 4.1 — Contador de Visitas com Arquivo (Fácil)
Crie um contador de visitas que persista em um arquivo `contador.txt`. Cada visita incrementa o valor. Use `file_get_contents` e `file_put_contents` com `LOCK_EX`.

### Exercício 4.2 — Processador de CSV (Médio)
Crie um script que leia um arquivo `dados.csv`, filtre linhas por uma condição (ex: idade > 25) e gere um novo CSV `filtrado.csv` com o resultado. Use `fgetcsv` e `fputcsv`.

### Exercício 4.3 — Mini Upload Center (Médio)
Crie um formulário para upload de até 3 arquivos simultâneos. Valide: tipo (apenas imagens), tamanho (máx 2 MB cada). Gere nomes únicos com `uniqid`. Guarde metadados em um arquivo JSON.

### Exercício 4.4 — Logger Rotativo (Difícil)
Crie uma classe `RotatingLogger` que escreva logs em arquivos rotativos. Quando o arquivo atual atingir 1 MB, ele é renomeado com a data e um novo arquivo é criado. O logger deve manter no máximo 5 arquivos antigos.

### Exercício 4.5 — Monitor de Diretório (Difícil)
Crie um script que monitore um diretório e detecte arquivos novos, modificados ou removidos desde a última execução. Salve um snapshot (array com nome e mtime) em um arquivo. Compare snapshots.

---

## Módulo 12: Formulários e Superglobais

### Exercício 5.1 — Formulário de Busca com GET (Fácil)
Crie uma página com um formulário de busca via GET. Quando o usuário pesquisa, filtre um array de dados em memória e exiba os resultados na mesma página, preservando o termo no campo de busca.

### Exercício 5.2 — Validator de Formulário Reutilizável (Médio)
Crie uma classe `FormValidator` que aceite regras encadeáveis:

```php
$validator = new FormValidator($_POST);
$validator->required('name')->minLength('name', 3)
          ->required('email')->email('email')
          ->required('password')->minLength('password'
```

### Exercício 5.3 — Formulário Multi-etapas (Médio)
Crie um formulário de cadastro em 3 etapas (Wizard) usando campos hidden para preservar dados entre etapas. Etapa 1: dados pessoais. Etapa 2: endereço. Etapa 3: confirmação e resumo.

### Exercício 5.4 — API REST Simples com `php://input` (Difícil)
Crie um endpoint que receba JSON via POST (`php://input`), valide os dados, insira em um banco SQLite e retorne JSON com o registro criado + código HTTP apropriado:
- Sucesso: 201 Created
- Erro de validação: 422 Unprocessable Entity
- Método errado: 405 Method Not Allowed

---

## Módulo 13: Sessões e Cookies

### Exercício 6.1 — Preferência de Tema (Fácil)
Crie uma página que leia um cookie `tema` e aplique classes CSS diferentes (claro/escuro). Um formulário permite alternar entre os temas. O cookie deve durar 30 dias.

### Exercício 6.2 — Carrinho de Compras com Sessão (Médio)
Expanda o exemplo do módulo 13 adicionando:
- Quantidade (incrementar/decrementar)
- Cupom de desconto (códigos pré-definidos)
- Cálculo de frete por faixa de valor

### Exercício 6.3 — Quiz com Progresso (Médio)
Crie um quiz de 5 perguntas armazenadas na sessão. Cada requisição exibe a pergunta atual e guarda a resposta. Ao final, mostre a pontuação. Use `session_regenerate_id` para evitar fixação.

### Exercício 6.4 — Rate Limiter por Sessão e IP (Difícil)
Crie um middleware de rate limiting que controle tentativas de acesso por sessão e por IP separadamente:
- 5 tentativas de login por sessão a cada 15 minutos
- 20 tentativas de login por IP a cada 15 minutos

Use cookies e arquivos para persistência. Retorne `429 Too Many Requests` quando exceder.

---

## Módulo 14: Banco de Dados com PDO

### Exercício 7.1 — CRUD de Products com SQLite (Fácil)
Crie uma tabela `produtos` (id, nome, preco, estoque, criado_em) e implemente todas as operações CRUD usando PDO + SQLite. Crie um script de teste que insira 3 produtos, liste todos, atualize um e deletar outro.

### Exercício 7.2 — Sistema de Busca com Filtros (Médio)
Crie um formulário de busca de produtos com filtros:
- Nome (LIKE parcial)
- Preço mínimo e máximo
- Ordenação por nome, preço, data

Construa a query dinamicamente, mas SEMPRE com prepared statements.

### Exercício 7.3 — Migração de Schema Automatizada (Médio)
Crie um sistema simples de migrations: uma tabela `migrations` armazena quais scripts já foram executados. Scripts na pasta `migrations/` são executados em ordem (001_create_tabela_x.sql, 002_adicionar_coluna_y.sql, etc.).

### Exercício 7.4 — Repositório Genérico com Query Builder (Difícil)
Crie uma classe `QueryBuilder` fluente que permita construir queries SQL de forma segura:

```php
$qb = new QueryBuilder($pdo);
$users = $qb->select(['id', 'name', 'email'])
               ->from('users')
               ->where('ativo', '=', 1)
               ->where('name', 'LIKE', '%João%')
               ->orderBy('name', 'ASC')
               ->limit(10)
               ->g
```

**Requisitos:** Todas as cláusulas `where` devem usar prepared statements. Métodos encadeáveis: `select()`, `from()`, `where()`, `whereIn()`, `orderBy()`, `limit()`, `offset()`, `get()`, `first()`, `count()`, `insert()`, `update()`, `delete()`.

---

## Módulo 15: Segurança

### Exercício 8.1 — Sanitizador de Output (Fácil)
Crie uma função `safe()` que receba um valor e retorne seguro para output HTML, considerando o contexto:
- Modo `html`: `htmlspecialchars`
- Modo `attr`: mesmo + escapa aspas
- Modo `js`: `json_encode` com flags de escape
- Modo `url`: `urlencode`

### Exercício 8.2 — Scanner de Vulnerabilidades (Médio)
Crie um script CLI que escaneie arquivos `.php` em um diretório em busca de padrões inseguros:
- `mysql_query`, `mysqli_query` sem prepared statements
- `$_GET`/`$_POST` concatenados em strings SQL
- `eval()`, `exec()`, `shell_exec()` sem sanitização
- echo sem `htmlspecialchars`

### Exercício 8.3 — Middleware de Headers de Segurança (Médio)
Crie uma classe `SecurityHeaders` que, quando chamada, adicione todos os headers de segurança recomendados (X-Content-Type-Options, X-Frame-Options, CSP, Referrer-Policy, Permissions-Policy, Strict-Transport-Security).

### Exercício 8.4 — Autenticação com Remember Me Seguro (Difícil)
Implemente o padrão "Remember Me" de forma segura:
- Token aleatório armazenado no banco (hash SHA-256)
- Cookie contém: `identificador:token`
- Na validação, compare `hash_equals` com o hash do banco
- Rotacione o token a cada uso bem-sucedido
- Invalide tokens antigos após rotação

---

## Módulos Combinados: Projetos Integradores

### Exercício 9.1 — Micro Blog com Arquivos (Médio)
Crie um micro blog que use apenas arquivos (sem banco de dados):

- `posts/` contém um arquivo `.json` por post
- O nome do arquivo é o slug do título
- Cada arquivo contém: `{"titulo":"...", "conteudo":"...", "data":"...", "autor":"..."}`
- Página inicial lista todos os posts (lê com `glob` + `file_get_contents`)
- Formulário cria novos posts (gera arquivo JSON com `file_put_contents` + `LOCK_EX`)

### Exercício 9.2 — API de Tasks RESTful (Difícil)
Crie uma API RESTful completa para o To-Do List do Projeto 2, usando JSON para comunicação:

| Método | Endpoint | Ação |
|--------|----------|------|
| GET | `/api/tarefas` | Listar todas |
| GET | `/api/tarefas/{id}` | Obter uma |
| POST | `/api/tarefas` | Criar |
| PUT | `/api/tarefas/{id}` | Atualizar |
| DELETE | `/api/tarefas/{id}` | Remover |
| PATCH | `/api/tarefas/{id}/toggle` | Alternar concluída |

**Requisitos:** SQLite + PDO, autenticação via token no header `Authorization: Bearer <token>`, rate limiting, cache de headers `ETag`.

### Exercício 9.3 — Sistema de Comentários com Moderação (Difícil)
Adicione ao Blog do Projeto 4 um sistema de comentários:

- Cada post tem comentários (tabela `comentarios`)
- Visitantes podem comentar (nome + texto)
- Admin pode aprovar/rejeitar comentários
- Anti-spam: limite de 3 comentários por IP a cada 10 minutos
- Anti-XSS: remover tags, escape output
- Os comentários aparecem abaixo do post, ordenados por data

---

## Gabarito e Dicas

### Exercício 1.1 (IMC)
```php
$imc = round($weight / ($height ** 2), 1);
$classification = match (true) {
    $imc < 18.5  => 'Abaixo do peso',
    $imc < 25    => 'Peso normal',
    $imc < 30    => 'Sobrepeso',
    default      => 'Obesidade
```

### Exercício 1.3 (CPF)
Dica: remova não dígitos com `preg_replace('/[^0-9]/', '', $cpf)`. O primeiro dígito usa pesos 10 a 2; o segundo usa pesos 11 a 2. Soma cada dígito × peso, calcula `(soma * 10) % 11 % 10`.

### Exercício 1.4 (Senha)
Dica: use `implode('', array_intersect_key($chars, array_flip(array_rand($chars, $length))))` ou construa manualmente garantindo cada classe de caractere.

### Exercício 2.1 (Slug)
```php
function slug(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s]+/', '-', trim($text));
    return strtolower($tex
```

### Exercício 2.2 (Emails)
Regex: `/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/`

### Exercício 3.1 (Conta Bancária)
Dica: Lance `InvalidArgumentException` para valores negativos e `RuntimeException` para saldo insuficiente.

### Exercício 4.1 (Contador)
```php
$counter = (int) file_get_contents('contador.txt');
$counter++;
file_put_contents('contador.txt', $counter, LOCK_EX);
echo "Visitas: {$count
```

### Exercício 4.3 (Upload Center)
Dica: Use `$_FILES['arquivos']` com `name="arquivos[]" multiple`. Itere com `for ($i = 0; $i < count($_FILES['arquivos']['name']); $i++)`.

### Exercício 5.4 (API REST)
```php
header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    ex
```

### Exercício 6.1 (Tema)
```php
setcookie('theme', $_POST['theme'], time() + 86400 * 30, '/', '', false, fa
```

### Exercício 6.4 (Rate Limiter)
Dica: Armazene um array `['attempts' => [], 'blocked_until' => null]` em arquivo JSON por chave. Verifique timestamps a cada requisição.

### Exercício 7.1 (CRUD Products)
```php
$pdo = new PDO('sqlite:products.sqlite');
$pdo->exec("CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, price REAL, stock INTEGER, created_at TEXT DEFAULT (datetime('now')
```

### Exercício 7.2 (Busca com Filtros)
Dica: Construa o WHERE incrementalmente com array de condições e array de parâmetros. Jamais concatene valores.

```php
$conditions = [];
$params = [];
if (!empty($name)) { $conditions[] = 'name LIKE :name'; $params[':name'] = "%{$name}%"; }
$sql = 'SELECT * FROM products' . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) :
```

### Exercício 7.4 (QueryBuilder)
Dica: Cada método retorna `$this`. O `get()` monta a SQL final e executa com `prepare + execute` usando o array de parâmetros acumulado.

### Exercício 8.1 (Sanitizador)
```php
function safe(mixed $amount, string $context = 'html'): string {
    return match ($context) {
        'html' => htmlspecialchars((string) $amount, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'js'   => json_encode($amount, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        'url'  => urlencode((string) $amount),
        default => htmlspecialchars((string) $amount, ENT_QUOTES, 'UTF-8'),
   
```

### Exercício 8.4 (Remember Me)
Dica: Use `bin2hex(random_bytes(32))` para gerar o token. Armazene no banco: `hash('sha256', $token)`. Na validação: `hash_equals($hashBanco, hash('sha256', $tokenDoCookie))`.

### Exercício 9.1 (Micro Blog)
Dica: `glob('posts/*.json')` para listar, `file_get_contents` para ler, `file_put_contents("posts/{$slug}.json", json_encode($data, JSON_PRETTY_PRINT), LOCK_EX)` para criar.

### Exercício 9.2 (API REST)
Dica: Use `parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)` e `explode('/', trim($path, '/'))` para extrair parâmetros da URL. Token: compare `hash_equals($tokenBanco, $_SERVER['HTTP_AUTHORIZATION'])` após remover "Bearer ".

### Exercício 9.3 (Comentários)
Dica: Estrutura da tabela: `id, post_id, nome, texto, aprovado, ip, criado_em`. Rate limit por IP similar ao Exercício 6.4. Admin acessa `/admin/comentarios` com lista de pendentes e botões aprovar/rejeitar.

---

## Como Usar Estes Exercícios

1. **Progressão recomendada:** Faça pelo menos 2 exercícios fáceis e 1 médio de cada módulo antes de avançar.
2. **Crie uma pasta `exercicios/`** com subpastas por módulo (`modulo01/`, `modulo02/`, etc.).
3. **Teste sempre:** Rode `php -f exercicio.php` ou acesse via servidor embutido.
4. **Versionamento:** Mantenha suas soluções no Git para acompanhar seu progresso.
5. **Desafios extras:** Tente combinar módulos — ex: adicionar upload de imagem (Mód. 11) ao sistema de login (Mód. 13).

---

## Checklist de Competências

| Competência | Exercícios |
|-------------|------------|
| Variáveis, tipos, operadores | 1.1, 1.2 |
| Estruturas de controle | 1.1, 1.2, 2.4 |
| Arrays | 1.3, 1.5, 2.3 |
| Funções | 1.3, 1.4, 2.1, 2.2, 2.4 |
| Strings e Regex | 2.1, 2.2, 2.3, 2.4 |
| OOP | 3.1, 3.2, 3.3, 3.4, 5.2, 7.4 |
| Exceções | 3.3 |
| Namespaces e Autoload | 3.4 |
| Arquivos | 4.1, 4.2, 4.3, 4.4, 4.5, 9.1 |
| Formulários e Superglobais | 5.1, 5.2, 5.3, 5.4 |
| Sessões e Cookies | 6.1, 6.2, 6.3, 6.4 |
| PDO e SQLite | 7.1, 7.2, 7.3, 7.4, 9.2, 9.3 |
| Segurança | 8.1, 8.2, 8.3, 8.4 |
| APIs REST | 5.4, 9.2 |
| Projetos Completos | 9.1, 9.2, 9.3 |
