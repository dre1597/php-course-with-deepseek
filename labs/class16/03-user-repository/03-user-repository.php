<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function save(string $name): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $name, string $email): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
        $stmt->execute(['name' => $name, 'email' => $email]);
        return (int) $this->pdo->lastInsertId();
    }
}
