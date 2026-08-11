<?php

class Product
{
    public int $id;
    public string $name;
    public float $price;
    public int $quantity;
    public string $created_at;
}

class ProductsCrud
{
    public function __construct(private PDO $pdo)
    {

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                name       TEXT    NOT NULL,
                price      REAL    NOT NULL,
                quantity   INTEGER NOT NULL,
                created_at TEXT    DEFAULT (datetime('now'))
            )
        ");
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM products');
        return $stmt->fetchAll(PDO::FETCH_CLASS, Product::class);
    }

    public function create(string $name, float $price, int $quantity): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO products (name, price, quantity) VALUES (:name, :price, :quantity)');
        $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':quantity' => $quantity,
        ]);
    }

    public function findOne(int $id): Product
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchObject(Product::class);
    }

    public function update(int $id, string $name, float $price, int $quantity): void
    {
        $stmt = $this->pdo->prepare('UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?');
        $stmt->execute([$name, $price, $quantity, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
