# Módulo 14: Banco de Dados com PDO

## Visão Geral

PDO (PHP Data Objects) é a interface recomendada para acesso a bancos de dados em PHP. Ela oferece uma camada de abstração que permite trabalhar com MySQL, PostgreSQL, SQLite, entre outros, usando a mesma API, além de **prepared statements** que protegem contra SQL injection.

> ⚠️ **Cuidado:** As funções `mysql_*` foram removidas no PHP 7. `mysqli_*` existe, mas **sempre prefira PDO** por sua flexibilidade e segurança.

---

## 1. Conexão com PDO

```php
<?php
// MySQL
$dsn = 'mysql:host=localhost;port=3306;dbname=curso_php;charset=utf8mb4';
$usuario = 'root';
$senha = '';
$opcoes = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
    echo "Conectado ao MySQL com sucesso!<br>\n";
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// SQLite (não precisa de servidor — arquivo único)
$dsnSqlite = 'sqlite:' . __DIR__ . '/banco.sqlite';
$pdoSqlite = new PDO($dsnSqlite, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
echo "Conectado ao SQLite!<br>\n";

// PostgreSQL
$dsnPg = 'pgsql:host=localhost;port=5432;dbname=curso_php';
$pdoPg = new PDO($dsnPg, 'postgres', 'senha', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
```

### Opções Importantes do PDO

| Opção | Valor | Descrição |
|-------|-------|-----------|
| `ATTR_ERRMODE` | `ERRMODE_EXCEPTION` | Lança exceção em erros |
| `ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC` | Retorna arrays associativos por padrão |
| `ATTR_EMULATE_PREPARES` | `false` | Usa prepared statements nativos do SGBD |
| `MYSQL_ATTR_INIT_COMMAND` | `SET NAMES utf8mb4` | Define charset na conexão MySQL |

---

## 2. PDO Driver-Specific Subclasses (PHP 8.4+)

> **PHP 8.4+**

O PHP 8.4 introduziu subclasses específicas para cada driver, permitindo tipagem mais precisa:

```php
<?php
// PHP 8.4+: subclasses específicas de driver
$pdoMysql  = new Pdo\Mysql('host=localhost;dbname=app;charset=utf8mb4', 'root', '');
$pdoSqlite = new Pdo\Sqlite('sqlite:' . __DIR__ . '/app.sqlite');
$pdoPgsql  = new Pdo\Pgsql('host=localhost;dbname=app', 'postgres', 'senha');

// Agora você pode tipar funções com o driver específico
function buscarUsuariosMySQL(Pdo\Mysql $pdo): array {
    return $pdo->query('SELECT * FROM usuarios')->fetchAll();
}

// $pdoMysql é do tipo Pdo\Mysql (que herda de PDO)
buscarUsuariosMySQL($pdoMysql); // OK
// buscarUsuariosMySQL($pdoSqlite); // Erro de tipo!
```

---

## 3. Criando Tabelas com PDO

```php
<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// SQL para criar tabelas
$sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        nome       TEXT    NOT NULL,
        email      TEXT    NOT NULL UNIQUE,
        senha      TEXT    NOT NULL,
        ativo      INTEGER DEFAULT 1,
        criado_em  TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        titulo     TEXT    NOT NULL,
        conteudo   TEXT    NOT NULL,
        criado_em  TEXT    DEFAULT (datetime('now')),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    );
";

$pdo->exec($sql);
echo "Tabelas criadas com sucesso!<br>\n";
```

> 💡 **Dica:** Use `exec()` para comandos SQL que **não retornam resultados** (CREATE, INSERT sem prepared statement, etc.). Retorna o número de linhas afetadas.

---

## 4. Prepared Statements (OBRIGATÓRIO!)

**NUNCA** concatene variáveis no SQL. Use sempre prepared statements.

```php
<?php
// ❌ NUNCA FAÇA ISSO!
$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id = {$id}";
// Um atacante pode injetar: 1; DROP TABLE usuarios; --

// ✅ SEMPRE use prepared statements
$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch();
```

### Placeholders Nomeados vs Interrogação

```php
<?php
// Nomeados (recomendado)
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email AND ativo = :ativo');
$stmt->execute([
    ':email' => 'joao@email.com',
    ':ativo' => 1,
]);

// Interrogação (posicional)
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = ?');
$stmt->execute(['joao@email.com', 1]);

// Misturar não funciona!
// ❌ $stmt->prepare('SELECT * FROM usuarios WHERE email = :email AND ativo = ?');
```

### `bindParam()` vs `bindValue()`

```php
<?php
// bindParam — vincula por REFERÊNCIA. A variável é lida no momento do execute().
$nome = 'João';
$stmt = $pdo->prepare('INSERT INTO usuarios (nome, email) VALUES (:nome, :email)');
$stmt->bindParam(':nome', $nome);
$stmt->bindValue(':email', 'joao@email.com');

$nome = 'Maria'; // altera a referência
$stmt->execute(); // Maria é inserida! (bindParam usou o valor atualizado)
// email continua 'joao@email.com' (bindValue fixou o valor no momento da chamada)

// bindParam com tipo
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
$stmt->bindParam(':nulo', $nulo, PDO::PARAM_NULL);
```

### Tipos de Parâmetro PDO

| Constante | Descrição |
|-----------|-----------|
| `PDO::PARAM_INT` | Inteiro |
| `PDO::PARAM_STR` | String (padrão) |
| `PDO::PARAM_BOOL` | Booleano |
| `PDO::PARAM_NULL` | NULL |
| `PDO::PARAM_LOB` | Large Object (arquivos binários) |

---

## 5. Fetch Modes (Modos de Recuperação)

```php
<?php
$stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios LIMIT 3');
$stmt->execute();

// FETCH_ASSOC — array associativo (RECOMENDADO)
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id']}: {$row['nome']} — {$row['email']}<br>\n";
}

// FETCH_OBJ — objeto stdClass
while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "{$obj->id}: {$obj->nome}<br>\n";
}

// FETCH_NUM — array indexado numericamente
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "{$row[0]}: {$row[1]}<br>\n";
}

// FETCH_BOTH — ambos (padrão, EVITE — duplica dados)
while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
    // $row[0] e $row['id'] apontam pro mesmo valor
}

// fetchAll — todas as linhas de uma vez
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($usuarios as $u) {
    echo "{$u['nome']}<br>\n";
}

// fetchColumn — valor de uma única coluna
$stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios');
$stmt->execute();
$total = $stmt->fetchColumn();
echo "Total de usuários: {$total}<br>\n";
```

### FETCH_CLASS — Hidrata em objeto de classe

```php
<?php
class Usuario {
    public int $id;
    public string $nome;
    public string $email;
}

$stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios LIMIT 5');
$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_CLASS, Usuario::class);
foreach ($usuarios as $u) {
    echo "{$u->nome} <{$u->email}><br>\n";
}
```

### Modo de Fetch Padrão

```php
<?php
// Define globalmente na conexão
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Agora todos os fetch() padrão retornam array associativo
$stmt = $pdo->query('SELECT * FROM usuarios');
$usuarios = $stmt->fetchAll();
```

---

## 6. CRUD Completo

### CREATE (INSERT)

```php
<?php
// Inserção simples
$stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
$stmt->execute([
    ':nome'  => 'João Silva',
    ':email' => 'joao@email.com',
    ':senha' => password_hash('senha123', PASSWORD_DEFAULT),
]);

$id = $pdo->lastInsertId();
echo "Usuário inserido com ID: {$id}<br>\n";

// Inserção múltipla (transação recomendada)
$usuarios = [
    ['nome' => 'Maria', 'email' => 'maria@email.com', 'senha' => password_hash('123456', PASSWORD_DEFAULT)],
    ['nome' => 'Pedro', 'email' => 'pedro@email.com', 'senha' => password_hash('abc123', PASSWORD_DEFAULT)],
];

$stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');

foreach ($usuarios as $u) {
    $stmt->execute([
        ':nome'  => $u['nome'],
        ':email' => $u['email'],
        ':senha' => $u['senha'],
    ]);
    echo "Inserido ID: " . $pdo->lastInsertId() . "<br>\n";
}
```

### READ (SELECT)

```php
<?php
// Busca por ID
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
$stmt->execute([':id' => 1]);
$usuario = $stmt->fetch();

if ($usuario) {
    echo "Nome: {$usuario['nome']}<br>\n";
    echo "Email: {$usuario['email']}<br>\n";
} else {
    echo "Usuário não encontrado.<br>\n";
}

// Busca com LIKE
$termo = '%joão%';
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nome LIKE :termo OR email LIKE :termo2');
$stmt->execute([':termo' => $termo, ':termo2' => $termo]);

// Busca com ORDER BY e LIMIT
$stmt = $pdo->prepare('SELECT * FROM usuarios ORDER BY nome ASC LIMIT :limite');
$stmt->bindValue(':limite', 10, PDO::PARAM_INT);
$stmt->execute();

// Busca condicional
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE ativo = :ativo');
$stmt->bindValue(':ativo', 1, PDO::PARAM_BOOL);
$stmt->execute();
```

### UPDATE

```php
<?php
$stmt = $pdo->prepare('UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id');
$stmt->execute([
    ':nome'  => 'João Silva Atualizado',
    ':email' => 'joao.novo@email.com',
    ':id'    => 1,
]);

$afetadas = $stmt->rowCount();
echo "{$afetadas} linha(s) atualizada(s).<br>\n";
```

### DELETE

```php
<?php
$stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
$stmt->execute([':id' => 3]);

$afetadas = $stmt->rowCount();
echo "{$afetadas} linha(s) removida(s).<br>\n";
```

---

## 7. Transações

Transações garantem que um conjunto de operações seja executado **atomicamente**: todas com sucesso, ou tudo é desfeito.

```php
<?php
try {
    $pdo->beginTransaction();

    // Insere o usuário
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute(['Carlos', 'carlos@email.com', password_hash('senha', PASSWORD_DEFAULT)]);
    $usuarioId = $pdo->lastInsertId();

    // Cria um post para o usuário
    $stmt = $pdo->prepare('INSERT INTO posts (usuario_id, titulo, conteudo) VALUES (?, ?, ?)');
    $stmt->execute([$usuarioId, 'Primeiro Post', 'Conteúdo do primeiro post.']);

    // Se tudo deu certo, confirma (commit)
    $pdo->commit();
    echo "Usuário e post criados com sucesso!<br>\n";

} catch (Exception $e) {
    // Se algo deu errado, desfaz tudo (rollback)
    $pdo->rollBack();
    echo "Erro: " . $e->getMessage() . " — Transação revertida.<br>\n";
}
```

### Exemplo real: Transferência entre contas

```php
<?php
function transferir(PDO $pdo, int $contaOrigem, int $contaDestino, float $valor): bool {
    try {
        $pdo->beginTransaction();

        // Verifica saldo da origem
        $stmt = $pdo->prepare('SELECT saldo FROM contas WHERE id = ? FOR UPDATE');
        $stmt->execute([$contaOrigem]);
        $saldo = $stmt->fetchColumn();

        if ($saldo === false) {
            throw new RuntimeException('Conta origem não encontrada.');
        }
        if ($saldo < $valor) {
            throw new RuntimeException('Saldo insuficiente.');
        }

        // Debita da origem
        $stmt = $pdo->prepare('UPDATE contas SET saldo = saldo - ? WHERE id = ?');
        $stmt->execute([$valor, $contaOrigem]);

        // Credita no destino
        $stmt = $pdo->prepare('UPDATE contas SET saldo = saldo + ? WHERE id = ?');
        $stmt->execute([$valor, $contaDestino]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transferência falhou: " . $e->getMessage());
        return false;
    }
}
```

---

## 8. Paginação com LIMIT + OFFSET

```php
<?php
// Configuração
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;
$offset = ($paginaAtual - 1) * $porPagina;

// Contar total de registros
$stmt = $pdo->query('SELECT COUNT(*) FROM posts');
$totalRegistros = $stmt->fetchColumn();
$totalPaginas = (int) ceil($totalRegistros / $porPagina);

// Buscar registros da página atual
$stmt = $pdo->prepare('SELECT * FROM posts ORDER BY criado_em DESC LIMIT :limite OFFSET :offset');
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

// Exibir resultados
foreach ($posts as $post) {
    echo "<h3>" . htmlspecialchars($post['titulo']) . "</h3>\n";
}

// Links de navegação
echo "<nav>\n";
for ($i = 1; $i <= $totalPaginas; $i++) {
    $classe = ($i === $paginaAtual) ? 'class="ativo"' : '';
    echo "<a href='?pagina={$i}' {$classe}>{$i}</a> ";
}
echo "</nav>\n";
echo "<p>Mostrando página {$paginaAtual} de {$totalPaginas}</p>\n";
```

---

## 9. SQL Injection: Como Prepared Statements Protegem

```php
<?php
// VULNERÁVEL — NUNCA faça:
$email = $_POST['email'];
$senha = $_POST['senha'];
$sql = "SELECT * FROM usuarios WHERE email = '{$email}' AND senha = '{$senha}'";

// Se o atacante digitar no campo email:
// ' OR 1=1 --
// O SQL se torna:
// SELECT * FROM usuarios WHERE email = '' OR 1=1 --' AND senha = 'qualquer'
// Isso retorna TODOS os usuários!

// Se o atacante digitar no campo email:
// '; DROP TABLE usuarios; --
// O SQL se torna:
// SELECT * FROM usuarios WHERE email = ''; DROP TABLE usuarios; --' AND senha = ''
// A tabela é DELETADA!

// PROTEGIDO — Prepared Statement:
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email AND senha = :senha');
$stmt->execute([':email' => $email, ':senha' => $senha]);

// O SGBD trata email e senha como VALORES LITERAIS, nunca como código SQL.
// Mesmo que o atacante digite ' OR 1=1 --, isso será tratado como uma
// string literal e não como SQL executável.
```

> 💡 **Dica:** Prepared statements enviam a estrutura da query e os dados separadamente ao SGBD. O banco compila a query primeiro, depois insere os parâmetros como valores literais. Isso torna a injeção impossível.

---

## 10. Conexão com SQLite para Projetos Pequenos

SQLite é perfeito para protótipos, aplicações de usuário único e estudos — não requer servidor de banco de dados, é um arquivo só.

```php
<?php
class ConexaoSQLite {
    private static ?PDO $instancia = null;

    public static function get(string $caminhoBanco = null): PDO {
        if (self::$instancia === null) {
            $caminho = $caminhoBanco ?? __DIR__ . '/database.sqlite';
            self::$instancia = new PDO('sqlite:' . $caminho, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Habilita foreign keys no SQLite
            self::$instancia->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instancia;
    }

    public static function inicializar(): void {
        $pdo = self::get();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                nome      TEXT NOT NULL,
                email     TEXT NOT NULL UNIQUE,
                senha     TEXT NOT NULL,
                criado_em TEXT DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }
}

// Uso
$pdo = ConexaoSQLite::get();
ConexaoSQLite::inicializar();
```

---

## 11. DSN Strings

### MySQL / MariaDB

```php
<?php
// Básico
'mysql:host=localhost;dbname=app;charset=utf8mb4'

// Com porta
'mysql:host=localhost;port=3307;dbname=app;charset=utf8mb4'

// Unix socket
'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=app;charset=utf8mb4'
```

### SQLite

```php
<?php
// Arquivo (cria se não existir)
'sqlite:/caminho/para/banco.sqlite'

// Em memória (some ao final da execução)
'sqlite::memory:'
```

### PostgreSQL

```php
<?php
'pgsql:host=localhost;port=5432;dbname=app;user=postgres;password=senha'
```

---

## 12. Exemplo Prático: Repositório Genérico

```php
<?php
abstract class Repositorio {
    public function __construct(
        protected PDO $pdo,
        protected string $tabela
    ) {}

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->tabela} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function buscarTodos(string $ordem = 'id ASC', int $limite = 100): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tabela} ORDER BY {$ordem} LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function inserir(array $dados): int {
        $colunas = implode(', ', array_keys($dados));
        $placeholders = ':' . implode(', :', array_keys($dados));

        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->tabela} ({$colunas}) VALUES ({$placeholders})"
        );
        $stmt->execute($dados);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): int {
        $sets = [];
        foreach ($dados as $coluna => $valor) {
            $sets[] = "{$coluna} = :{$coluna}";
        }
        $sets = implode(', ', $sets);
        $dados[':id'] = $id;

        $stmt = $this->pdo->prepare(
            "UPDATE {$this->tabela} SET {$sets} WHERE id = :id"
        );
        $stmt->execute($dados);
        return $stmt->rowCount();
    }

    public function deletar(int $id): int {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tabela} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function contar(): int {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->tabela}")->fetchColumn();
    }
}

// Repositório de Usuários
class UsuarioRepositorio extends Repositorio {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'usuarios');
    }

    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function buscarAtivos(): array {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Uso
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$repo = new UsuarioRepositorio($pdo);
$repo->inserir(['nome' => 'Ana', 'email' => 'ana@email.com', 'senha' => password_hash('123', PASSWORD_DEFAULT)]);
$usuario = $repo->buscarPorEmail('ana@email.com');
print_r($usuario);

$todos = $repo->buscarAtivos();
echo "Usuários ativos: " . count($todos) . "<br>\n";
```

---

## 📚 Referências

- [PHP: PDO Manual](https://www.php.net/manual/pt_BR/book.pdo.php)
- [PHP: PDOStatement](https://www.php.net/manual/pt_BR/class.pdostatement.php)
- [phpdelusions.net/pdo — The only proper PDO tutorial](https://phpdelusions.net/pdo)
- [PHP: PDO drivers — MySQL, SQLite, PostgreSQL](https://www.php.net/manual/pt_BR/pdo.drivers.php)
- [PHP 8.4: PDO driver-specific subclasses](https://www.php.net/manual/pt_BR/migration84.new-features.php)
- [OWASP: SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
