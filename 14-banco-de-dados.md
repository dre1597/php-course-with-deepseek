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
$user = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
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
$pdoPg = new PDO($dsnPg, 'postgres', 'password', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
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
$pdoPgsql  = new Pdo\Pgsql('host=localhost;dbname=app', 'postgres', 'password');

// Agora você pode tipar funções com o driver específico
function findUsersMySQL(Pdo\Mysql $pdo): array {
    return $pdo->query('SELECT * FROM users')->fetchAll();
}

// $pdoMysql é do tipo Pdo\Mysql (que herda de PDO)
findUsersMySQL($pdoMysql); // OK
// findUsersMySQL($pdoSqlite); // Erro de 
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
    CREATE TABLE IF NOT EXISTS users (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        name       TEXT    NOT NULL,
        email      TEXT    NOT NULL UNIQUE,
        password      TEXT    NOT NULL,
        ativo      INTEGER DEFAULT 1,
        created_at  TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titulo     TEXT    NOT NULL,
        conteudo   TEXT    NOT NULL,
        created_at  TEXT    DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
";

$pdo->exec($sql);
echo "Tabelas criadas com sucesso!<br
```

> 💡 **Dica:** Use `exec()` para comandos SQL que **não retornam resultados** (CREATE, INSERT sem prepared statement, etc.). Retorna o número de linhas afetadas.

---

## 4. Prepared Statements (OBRIGATÓRIO!)

**NUNCA** concatene variáveis no SQL. Use sempre prepared statements.

```php
<?php
// ❌ NUNCA FAÇA ISSO!
$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = {$id}";
// Um atacante pode injetar: 1; DROP TABLE users; --

// ✅ SEMPRE use prepared statements
$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fet
```

### Placeholders Nomeados vs Interrogação

```php
<?php
// Nomeados (recomendado)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND ativo = :ativo');
$stmt->execute([
    ':email' => 'joao@email.com',
    ':ativo' => 1,
]);

// Interrogação (posicional)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND ativo = ?');
$stmt->execute(['joao@email.com', 1]);

// Misturar não funciona!
// ❌ $stmt->prepare('SELECT * FROM users WHERE email = :email AND ativo =
```

### `bindParam()` vs `bindValue()`

```php
<?php
// bindParam — vincula por REFERÊNCIA. A variável é lida no momento do execute().
$name = 'João';
$stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
$stmt->bindParam(':name', $name);
$stmt->bindValue(':email', 'joao@email.com');

$name = 'Maria'; // altera a referência
$stmt->execute(); // Maria é inserida! (bindParam usou o valor atualizado)
// email continua 'joao@email.com' (bindValue fixou o valor no momento da chamada)

// bindParam com tipo
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
$stmt->bindParam(':nulo', $nulo, PDO::PARAM_N
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
$stmt = $pdo->prepare('SELECT id, name, email FROM users LIMIT 3');
$stmt->execute();

// FETCH_ASSOC — array associativo (RECOMENDADO)
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id']}: {$row['name']} — {$row['email']}<br>\n";
}

// FETCH_OBJ — objeto stdClass
while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "{$obj->id}: {$obj->name}<br>\n";
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
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "{$u['name']}<br>\n";
}

// fetchColumn — valor de uma única coluna
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
$stmt->execute();
$total = $stmt->fetchColumn();
echo "Total de usuários: {$total}<br
```

### FETCH_CLASS — Hidrata em objeto de classe

```php
<?php
class User {
    public int $id;
    public string $name;
    public string $email;
}

$stmt = $pdo->prepare('SELECT id, name, email FROM users LIMIT 5');
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_CLASS, User::class);
foreach ($users as $u) {
    echo "{$u->name} <{$u->email}><br>\
```

### Modo de Fetch Padrão

```php
<?php
// Define globalmente na conexão
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Agora todos os fetch() padrão retornam array associativo
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchA
```

---

## 6. CRUD Completo

### CREATE (INSERT)

```php
<?php
// Inserção simples
$stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
$stmt->execute([
    ':name'  => 'João Silva',
    ':email' => 'joao@email.com',
    ':password' => password_hash('senha123', PASSWORD_DEFAULT),
]);

$id = $pdo->lastInsertId();
echo "Usuário inserido com ID: {$id}<br>\n";

// Inserção múltipla (transação recomendada)
$users = [
    ['name' => 'Maria', 'email' => 'maria@email.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['name' => 'Pedro', 'email' => 'pedro@email.com', 'password' => password_hash('abc123', PASSWORD_DEFAULT)],
];

$stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');

foreach ($users as $u) {
    $stmt->execute([
        ':name'  => $u['name'],
        ':email' => $u['email'],
        ':password' => $u['password'],
    ]);
    echo "Inserido ID: " . $pdo->lastInsertId() . "<br>\
```

### READ (SELECT)

```php
<?php
// Busca por ID
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => 1]);
$user = $stmt->fetch();

if ($user) {
    echo "Nome: {$user['name']}<br>\n";
    echo "Email: {$user['email']}<br>\n";
} else {
    echo "Usuário não encontrado.<br>\n";
}

// Busca com LIKE
$term = '%joão%';
$stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :term OR email LIKE :term2');
$stmt->execute([':term' => $term, ':term2' => $term]);

// Busca com ORDER BY e LIMIT
$stmt = $pdo->prepare('SELECT * FROM users ORDER BY name ASC LIMIT :limite');
$stmt->bindValue(':limite', 10, PDO::PARAM_INT);
$stmt->execute();

// Busca condicional
$stmt = $pdo->prepare('SELECT * FROM users WHERE ativo = :ativo');
$stmt->bindValue(':ativo', 1, PDO::PARAM_BOOL);
$stmt->execu
```

### UPDATE

```php
<?php
$stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
$stmt->execute([
    ':name'  => 'João Silva Atualizado',
    ':email' => 'joao.novo@email.com',
    ':id'    => 1,
]);

$affected = $stmt->rowCount();
echo "{$affected} linha(s) atualizada(s).<br
```

### DELETE

```php
<?php
$stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$stmt->execute([':id' => 3]);

$affected = $stmt->rowCount();
echo "{$affected} linha(s) removida(s).<br
```

---

## 7. Transações

Transações garantem que um conjunto de operações seja executado **atomicamente**: todas com sucesso, ou tudo é desfeito.

```php
<?php
try {
    $pdo->beginTransaction();

    // Insere o usuário
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Carlos', 'carlos@email.com', password_hash('password', PASSWORD_DEFAULT)]);
    $userId = $pdo->lastInsertId();

    // Cria um post para o usuário
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, titulo, conteudo) VALUES (?, ?, ?)');
    $stmt->execute([$userId, 'Primeiro Post', 'Conteúdo do primeiro post.']);

    // Se tudo deu certo, confirma (commit)
    $pdo->commit();
    echo "Usuário e post criados com sucesso!<br>\n";

} catch (Exception $e) {
    // Se algo deu errado, desfaz tudo (rollback)
    $pdo->rollBack();
    echo "Erro: " . $e->getMessage() . " — Transação revertida.<br>\
```

### Exemplo real: Transferência entre contas

```php
<?php
function transfer(PDO $pdo, int $sourceAccount, int $destAccount, float $amount): bool {
    try {
        $pdo->beginTransaction();

        // Verifica balance da origem
        $stmt = $pdo->prepare('SELECT balance FROM contas WHERE id = ? FOR UPDATE');
        $stmt->execute([$sourceAccount]);
        $balance = $stmt->fetchColumn();

        if ($balance === false) {
            throw new RuntimeException('Conta origem não encontrada.');
        }
        if ($balance < $amount) {
            throw new RuntimeException('Saldo insuficiente.');
        }

        // Debita da origem
        $stmt = $pdo->prepare('UPDATE contas SET balance = balance - ? WHERE id = ?');
        $stmt->execute([$amount, $sourceAccount]);

        // Credita no destino
        $stmt = $pdo->prepare('UPDATE contas SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$amount, $destAccount]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transferência falhou: " . $e->getMessage());
        return false;
  
```

---

## 8. Paginação com LIMIT + OFFSET

```php
<?php
// Configuração
$currentPage = max(1, (int) ($_GET['pagina'] ?? 1));
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Contar total de registros
$stmt = $pdo->query('SELECT COUNT(*) FROM posts');
$totalRecords = $stmt->fetchColumn();
$totalPages = (int) ceil($totalRecords / $perPage);

// Buscar registros da página atual
$stmt = $pdo->prepare('SELECT * FROM posts ORDER BY created_at DESC LIMIT :limite OFFSET :offset');
$stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

// Exibir resultados
foreach ($posts as $post) {
    echo "<h3>" . htmlspecialchars($post['title']) . "</h3>\n";
}

// Links de navegação
echo "<nav>\n";
for ($i = 1; $i <= $totalPages; $i++) {
    $class = ($i === $currentPage) ? 'class="ativo"' : '';
    echo "<a href='?pagina={$i}' {$class}>{$i}</a> ";
}
echo "</nav>\n";
echo "<p>Mostrando página {$currentPage} de {$totalPages}</p
```

---

## 9. SQL Injection: Como Prepared Statements Protegem

```php
<?php
// VULNERÁVEL — NUNCA faça:
$email = $_POST['email'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE email = '{$email}' AND password = '{$password}'";

// Se o atacante digitar no campo email:
// ' OR 1=1 --
// O SQL se torna:
// SELECT * FROM users WHERE email = '' OR 1=1 --' AND password = 'qualquer'
// Isso retorna TODOS os usuários!

// Se o atacante digitar no campo email:
// '; DROP TABLE users; --
// O SQL se torna:
// SELECT * FROM users WHERE email = ''; DROP TABLE users; --' AND password = ''
// A tabela é DELETADA!

// PROTEGIDO — Prepared Statement:
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND password = :password');
$stmt->execute([':email' => $email, ':password' => $password]);

// O SGBD trata email e password como VALORES LITERAIS, nunca como código SQL.
// Mesmo que o atacante digite ' OR 1=1 --, isso será tratado como uma
// string literal e não como SQL execut
```

> 💡 **Dica:** Prepared statements enviam a estrutura da query e os dados separadamente ao SGBD. O banco compila a query primeiro, depois insere os parâmetros como valores literais. Isso torna a injeção impossível.

---

## 10. Conexão com SQLite para Projetos Pequenos

SQLite é perfeito para protótipos, aplicações de usuário único e estudos — não requer servidor de banco de dados, é um arquivo só.

```php
<?php
class SQLiteConnection {
    private static ?PDO $instance = null;

    public static function get(string $dbPath = null): PDO {
        if (self::$instance === null) {
            $path = $dbPath ?? __DIR__ . '/database.sqlite';
            self::$instance = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Habilita foreign keys no SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instance;
    }

    public static function inicializar(): void {
        $pdo = self::get();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                name      TEXT NOT NULL,
                email     TEXT NOT NULL UNIQUE,
                password     TEXT NOT NULL,
                created_at TEXT DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }
}

// Uso
$pdo = SQLiteConnection::get();
SQLiteConnection::inicializ
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
'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=app;charset=utf
```

### SQLite

```php
<?php
// Arquivo (cria se não existir)
'sqlite:/caminho/para/banco.sqlite'

// Em memória (some ao final da execução)
'sqlite::mem
```

### PostgreSQL

```php
<?php
'pgsql:host=localhost;port=5432;dbname=app;user=postgres;password=s
```

---

## 12. Exemplo Prático: Repositório Genérico

```php
<?php
abstract class Repository {
    public function __construct(
        protected PDO $pdo,
        protected string $table
    ) {}

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAll(string $order = 'id ASC', int $limit = 100): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$order} LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): int {
        $sets = [];
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = :{$column}";
        }
        $sets = implode(', ', $sets);
        $data[':id'] = $id;

        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET {$sets} WHERE id = :id"
        );
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function delete(int $id): int {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function count(): int {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }
}

// Repositório de Usuários
class UserRepository extends Repository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'users');
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findActive(): array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE ativo = 1 ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Uso
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$repo = new UserRepository($pdo);
$repo->insert(['name' => 'Ana', 'email' => 'ana@email.com', 'password' => password_hash('123', PASSWORD_DEFAULT)]);
$user = $repo->findByEmail('ana@email.com');
print_r($user);

$all = $repo->findActive();
echo "Usuários ativos: " . count($all) . "<br
```

---

## 📚 Referências

- [PHP: PDO Manual](https://www.php.net/manual/pt_BR/book.pdo.php)
- [PHP: PDOStatement](https://www.php.net/manual/pt_BR/class.pdostatement.php)
- [phpdelusions.net/pdo — The only proper PDO tutorial](https://phpdelusions.net/pdo)
- [PHP: PDO drivers — MySQL, SQLite, PostgreSQL](https://www.php.net/manual/pt_BR/pdo.drivers.php)
- [PHP 8.4: PDO driver-specific subclasses](https://www.php.net/manual/pt_BR/migration84.new-features.php)
- [OWASP: SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
