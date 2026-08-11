<?php

class Product
{
    public int $id;
    public string $name;
    public float $price;
    public int $quantity;
    public string $created_at;
}

class SearchFilter
{
    public function __construct(private readonly PDO $pdo)
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

    public function search(
        ?string $name = null,
        ?float  $minPrice = null,
        ?float  $maxPrice = null,
        string  $orderBy = "id",
        string  $direction = "ASC",
    ): array
    {
        $conditions = [];
        $params = [];

        if ($name !== null && $name !== '') {
            $conditions[] = "name LIKE ?";
            $params[] = "%{$name}%";
        }

        if ($minPrice !== null) {
            $conditions[] = 'price >= ?';
            $params[] = $minPrice;
        }

        if ($maxPrice !== null) {
            $conditions[] = 'price <= ?';
            $params[] = $maxPrice;
        }

        $allowedOrderBy = ['id', 'name', 'price', 'quantity', 'created_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy, true) ? $orderBy : 'id';

        $direction = strtoupper($direction);
        $direction = in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC';

        $where = $conditions
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        $stmt = $this->pdo->prepare(
            "SELECT *
                    FROM products
                    $where
                    ORDER BY $orderBy $direction"
        );

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Product::class);
    }
}
