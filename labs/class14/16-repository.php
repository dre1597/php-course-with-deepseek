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
echo "Usuários ativos: " . count($all) . "<br>\n";
