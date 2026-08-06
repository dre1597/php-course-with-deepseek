# Module 14: Database with PDO

## Overview

PDO (PHP Data Objects) is the recommended interface for database access in PHP. It provides an abstraction layer that lets you work with MySQL, PostgreSQL, SQLite, and others using the same API, plus **prepared statements** that protect against SQL injection.

> **Warning:** The `mysql_*` functions were removed in PHP 7. `mysqli_*` still exists, but **always prefer PDO** for its flexibility and security.

---

## 1. Connecting with PDO

```php
<?php
// MySQL
$dsn = 'mysql:host=localhost;port=3306;dbname=php_course;charset=utf8mb4';
$user = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    echo "Connected to MySQL successfully!<br>\n";
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}

// SQLite (no server needed — single file)
$dsnSqlite = 'sqlite:' . __DIR__ . '/database.sqlite';
$pdoSqlite = new PDO($dsnSqlite, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
echo "Connected to SQLite!<br>\n";

// PostgreSQL
$dsnPg = 'pgsql:host=localhost;port=5432;dbname=php_course';
$pdoPg = new PDO($dsnPg, 'postgres', 'password', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
```

### Important PDO Options

| Option | Value | Description |
|-------|-------|-----------|
| `ATTR_ERRMODE` | `ERRMODE_EXCEPTION` | Throws exceptions on errors |
| `ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC` | Returns associative arrays by default |
| `ATTR_EMULATE_PREPARES` | `false` | Uses native DBMS prepared statements |
| `MYSQL_ATTR_INIT_COMMAND` | `SET NAMES utf8mb4` | Sets charset on MySQL connection |

---

## 2. PDO Driver-Specific Subclasses (PHP 8.4+)

> **PHP 8.4+**

PHP 8.4 introduced driver-specific subclasses, enabling more precise typing:

```php
<?php
// PHP 8.4+: driver-specific subclasses
$pdoMysql  = new Pdo\Mysql('host=localhost;dbname=app;charset=utf8mb4', 'root', '');
$pdoSqlite = new Pdo\Sqlite('sqlite:' . __DIR__ . '/app.sqlite');
$pdoPgsql  = new Pdo\Pgsql('host=localhost;dbname=app', 'postgres', 'password');

// Now you can type-hint functions with the specific driver
function findUsersMySQL(Pdo\Mysql $pdo): array {
    return $pdo->query('SELECT * FROM users')->fetchAll();
}

// $pdoMysql is of type Pdo\Mysql (which inherits from PDO)
findUsersMySQL($pdoMysql); // OK
// findUsersMySQL($pdoSqlite); // Type error
```

---

## 3. Creating Tables with PDO

```php
<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// SQL to create tables
$sql = "
    CREATE TABLE IF NOT EXISTS users (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        name       TEXT    NOT NULL,
        email      TEXT    NOT NULL UNIQUE,
        password   TEXT    NOT NULL,
        active     INTEGER DEFAULT 1,
        created_at TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id    INTEGER NOT NULL,
        title      TEXT    NOT NULL,
        content    TEXT    NOT NULL,
        created_at TEXT    DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
";

$pdo->exec($sql);
echo "Tables created successfully!<br>\n";
```

> **Tip:** Use `exec()` for SQL commands that **do not return results** (CREATE, INSERT without prepared statement, etc.). It returns the number of affected rows.

---

## 4. Prepared Statements (MANDATORY!)

**NEVER** concatenate variables into SQL. Always use prepared statements.

```php
<?php
// ❌ NEVER DO THIS!
$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = {$id}";
// An attacker can inject: 1; DROP TABLE users; --

// ✅ ALWAYS use prepared statements
$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch();
```

### Named vs Question Mark Placeholders

```php
<?php
// Named (recommended)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND active = :active');
$stmt->execute([
    ':email'  => 'john@email.com',
    ':active' => 1,
]);

// Question mark (positional)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND active = ?');
$stmt->execute(['john@email.com', 1]);

// Mixing them won't work!
// ❌ $stmt->prepare('SELECT * FROM users WHERE email = :email AND active = ?');
```

### `bindParam()` vs `bindValue()`

```php
<?php
// bindParam — binds by REFERENCE. The variable is read at execute() time.
$name = 'John';
$stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
$stmt->bindParam(':name', $name);
$stmt->bindValue(':email', 'john@email.com');

$name = 'Mary'; // changes the reference
$stmt->execute(); // Mary is inserted! (bindParam used the updated value)
// email remains 'john@email.com' (bindValue fixed the value at call time)

// bindParam with type
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':active', $active, PDO::PARAM_BOOL);
$stmt->bindParam(':nullable', $nullable, PDO::PARAM_NULL);
```

### PDO Parameter Types

| Constant | Description |
|-----------|-----------|
| `PDO::PARAM_INT` | Integer |
| `PDO::PARAM_STR` | String (default) |
| `PDO::PARAM_BOOL` | Boolean |
| `PDO::PARAM_NULL` | NULL |
| `PDO::PARAM_LOB` | Large Object (binary files) |

---

## 5. Fetch Modes

```php
<?php
$stmt = $pdo->prepare('SELECT id, name, email FROM users LIMIT 3');
$stmt->execute();

// FETCH_ASSOC — associative array (RECOMMENDED)
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id']}: {$row['name']} — {$row['email']}<br>\n";
}

// FETCH_OBJ — stdClass object
while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "{$obj->id}: {$obj->name}<br>\n";
}

// FETCH_NUM — numerically indexed array
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "{$row[0]}: {$row[1]}<br>\n";
}

// FETCH_BOTH — both (default, AVOID — duplicates data)
while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
    // $row[0] and $row['id'] point to the same value
}

// fetchAll — all rows at once
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "{$u['name']}<br>\n";
}

// fetchColumn — value from a single column
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
$stmt->execute();
$total = $stmt->fetchColumn();
echo "Total users: {$total}<br>\n";
```

**`FETCH_ASSOC`** returns `['id' => 1, 'name' => 'John']` — you access columns by name (`$row['name']`). It's the recommended default: predictable, performant (no duplicated data), and doesn't break if the column order changes.

**`FETCH_OBJ`** returns a generic `stdClass` — `$row->name`. Clean syntax but no type safety from the IDE.

**`FETCH_NUM`** returns `[0 => 1, 1 => 'John']` — `$row[0]`. Brittle: if the query column order changes, your code silently breaks.

**`FETCH_BOTH`** returns both numeric and associative keys — `$row[0]` AND `$row['id']` for the same value. It's the PDO default but wastes memory. Always override it with `FETCH_ASSOC`.

**`FETCH_CLASS`** hydrates into YOUR typed class (see below) — real objects with `int $id`, `string $name`, etc. Best for type safety.

### FETCH_CLASS — Hydrate into a class instance

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
    echo "{$u->name} <{$u->email}><br>\n";
}
```

### Default Fetch Mode

```php
<?php
// Set globally on the connection
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Now all default fetch() calls return associative arrays
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();
```

---

## 6. Full CRUD

### CREATE (INSERT)

```php
<?php
// Simple insert
$stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
$stmt->execute([
    ':name'     => 'John Doe',
    ':email'    => 'john@email.com',
    ':password' => password_hash('pass123', PASSWORD_DEFAULT),
]);

$id = $pdo->lastInsertId();
echo "User inserted with ID: {$id}<br>\n";

// Multiple insert (transaction recommended)
$users = [
    ['name' => 'Mary', 'email' => 'mary@email.com', 'password' => password_hash('123456', PASSWORD_DEFAULT)],
    ['name' => 'Peter', 'email' => 'peter@email.com', 'password' => password_hash('abc123', PASSWORD_DEFAULT)],
];

$stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');

foreach ($users as $u) {
    $stmt->execute([
        ':name'     => $u['name'],
        ':email'    => $u['email'],
        ':password' => $u['password'],
    ]);
    echo "Inserted ID: " . $pdo->lastInsertId() . "<br>\n";
}
```

### READ (SELECT)

```php
<?php
// Find by ID
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => 1]);
$user = $stmt->fetch();

if ($user) {
    echo "Name: {$user['name']}<br>\n";
    echo "Email: {$user['email']}<br>\n";
} else {
    echo "User not found.<br>\n";
}

// Search with LIKE
$term = '%john%';
$stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :term OR email LIKE :term2');
$stmt->execute([':term' => $term, ':term2' => $term]);

// Query with ORDER BY and LIMIT
$stmt = $pdo->prepare('SELECT * FROM users ORDER BY name ASC LIMIT :limit');
$stmt->bindValue(':limit', 10, PDO::PARAM_INT);
$stmt->execute();

// Conditional query
$stmt = $pdo->prepare('SELECT * FROM users WHERE active = :active');
$stmt->bindValue(':active', 1, PDO::PARAM_BOOL);
$stmt->execute();
```

### UPDATE

```php
<?php
$stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
$stmt->execute([
    ':name'  => 'John Doe Updated',
    ':email' => 'john.new@email.com',
    ':id'    => 1,
]);

$affected = $stmt->rowCount();
echo "{$affected} row(s) updated.<br>\n";
```

### DELETE

```php
<?php
$stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$stmt->execute([':id' => 3]);

$affected = $stmt->rowCount();
echo "{$affected} row(s) deleted.<br>\n";
```

---

## 7. Transactions

Transactions ensure a set of operations executes **atomically**: all succeed, or everything is rolled back.

```php
<?php
try {
    $pdo->beginTransaction();

    // Insert the user
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Charles', 'charles@email.com', password_hash('password', PASSWORD_DEFAULT)]);
    $userId = $pdo->lastInsertId();

    // Create a post for the user
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)');
    $stmt->execute([$userId, 'First Post', 'Content of the first post.']);

    // If everything went well, commit
    $pdo->commit();
    echo "User and post created successfully!<br>\n";

} catch (Exception $e) {
    // If something went wrong, roll back everything
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . " — Transaction reverted.<br>\n";
}
```

### Real-world example: Transfer between accounts

```php
<?php
function transfer(PDO $pdo, int $sourceAccount, int $destAccount, float $amount): bool {
    try {
        $pdo->beginTransaction();

        // Check source balance
        $stmt = $pdo->prepare('SELECT balance FROM accounts WHERE id = ? FOR UPDATE');
        $stmt->execute([$sourceAccount]);
        $balance = $stmt->fetchColumn();

        if ($balance === false) {
            throw new RuntimeException('Source account not found.');
        }
        if ($balance < $amount) {
            throw new RuntimeException('Insufficient balance.');
        }

        // Debit from source
        $stmt = $pdo->prepare('UPDATE accounts SET balance = balance - ? WHERE id = ?');
        $stmt->execute([$amount, $sourceAccount]);

        // Credit to destination
        $stmt = $pdo->prepare('UPDATE accounts SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$amount, $destAccount]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transfer failed: " . $e->getMessage());
        return false;
    }
}
```

---

## 8. Pagination with LIMIT + OFFSET

```php
<?php
// Configuration
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Count total records
$stmt = $pdo->query('SELECT COUNT(*) FROM posts');
$totalRecords = $stmt->fetchColumn();
$totalPages = (int) ceil($totalRecords / $perPage);

// Fetch records for the current page
$stmt = $pdo->prepare('SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

// Display results
foreach ($posts as $post) {
    echo "<h3>" . htmlspecialchars($post['title']) . "</h3>\n";
}

// Navigation links
echo "<nav>\n";
for ($i = 1; $i <= $totalPages; $i++) {
    $class = ($i === $currentPage) ? 'class="active"' : '';
    echo "<a href='?page={$i}' {$class}>{$i}</a> ";
}
echo "</nav>\n";
echo "<p>Showing page {$currentPage} of {$totalPages}</p>\n";
```

---

## 9. SQL Injection: How Prepared Statements Protect You

```php
<?php
// VULNERABLE — NEVER do this:
$email = $_POST['email'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE email = '{$email}' AND password = '{$password}'";

// If the attacker types into the email field:
// ' OR 1=1 --
// The SQL becomes:
// SELECT * FROM users WHERE email = '' OR 1=1 --' AND password = 'whatever'
// This returns ALL users!

// If the attacker types into the email field:
// '; DROP TABLE users; --
// The SQL becomes:
// SELECT * FROM users WHERE email = ''; DROP TABLE users; --' AND password = ''
// The table is DELETED!

// PROTECTED — Prepared Statement:
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND password = :password');
$stmt->execute([':email' => $email, ':password' => $password]);

// The DBMS treats email and password as LITERAL VALUES, never as SQL code.
// Even if the attacker types ' OR 1=1 --, it will be treated as a
// string literal and not as executable SQL.
```

> **Tip:** Prepared statements send the query structure and the data separately to the DBMS. The database compiles the query first, then inserts the parameters as literal values. This makes injection impossible.

---

## 10. SQLite Connection for Small Projects

SQLite is perfect for prototypes, single-user applications, and study projects — it doesn't require a database server, just a single file.

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
            // Enable foreign keys on SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instance;
    }

    public static function initialize(): void {
        $pdo = self::get();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                name       TEXT NOT NULL,
                email      TEXT NOT NULL UNIQUE,
                password   TEXT NOT NULL,
                created_at TEXT DEFAULT (datetime('now', 'localtime'))
            );
        ");
    }
}

// Usage
$pdo = SQLiteConnection::get();
SQLiteConnection::initialize();
```

---

## 11. DSN Strings

### MySQL / MariaDB

```php
<?php
// Basic
'mysql:host=localhost;dbname=app;charset=utf8mb4'

// With port
'mysql:host=localhost;port=3307;dbname=app;charset=utf8mb4'

// Unix socket
'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=app;charset=utf8mb4'
```

### SQLite

```php
<?php
// File (creates if it doesn't exist)
'sqlite:/path/to/database.sqlite'

// In-memory (gone at end of execution)
'sqlite::memory:'
```

### PostgreSQL

```php
<?php
'pgsql:host=localhost;port=5432;dbname=app;user=postgres;password=secret'
```

---

## 12. Practical Example: Generic Repository

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
            "SELECT * FROM {$this->table} ORDER BY {$order} LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
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

// User Repository
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
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE active = 1 ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Usage
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$repo = new UserRepository($pdo);
$repo->insert(['name' => 'Anne', 'email' => 'anne@email.com', 'password' => password_hash('123', PASSWORD_DEFAULT)]);
$user = $repo->findByEmail('anne@email.com');
print_r($user);

$all = $repo->findActive();
echo "Active users: " . count($all) . "<br>\n";
```

---

---
## Navigation

- [← Module 13: Sessions and Cookies](./13-sessoes-e-cookies.md)
- [→ Module 15: Security](./15-seguranca.md)

---

## References

- [PHP: PDO Manual](https://www.php.net/manual/en/book.pdo.php)
- [PHP: PDOStatement](https://www.php.net/manual/en/class.pdostatement.php)
- [phpdelusions.net/pdo — The only proper PDO tutorial](https://phpdelusions.net/pdo)
- [PHP: PDO drivers — MySQL, SQLite, PostgreSQL](https://www.php.net/manual/en/pdo.drivers.php)
- [PHP 8.4: PDO driver-specific subclasses](https://www.php.net/manual/en/migration84.new-features.php)
- [OWASP: SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
