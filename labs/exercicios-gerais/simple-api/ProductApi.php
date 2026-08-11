<?php

class ProductApi
{
    public static function handle(string $dbPath): void
    {
        self::process($dbPath, $_SERVER['REQUEST_METHOD'] ?? 'GET', file_get_contents('php://input'));
    }

    public static function process(string $dbPath, string $method, string $rawInput): void
    {
        if ($method !== 'POST') {
            self::respond(405, ['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode($rawInput, true);

        if (!is_array($input)) {
            self::respond(422, ['error' => 'Invalid JSON']);
            return;
        }

        $errors = self::validate($input);
        if (!empty($errors)) {
            self::respond(422, ['errors' => $errors]);
            return;
        }

        $db = new SQLite3($dbPath);
        self::ensureTable($db);
        self::insert($db, $input);
        $db->close();

        self::respond(201, $input);
    }

    public static function validate(array $data): array
    {
        $errors = [];

        $name = $data['name'] ?? '';
        if (trim($name) === '') {
            $errors['name'] = 'name is required';
        }

        $price = $data['price'] ?? null;
        if ($price === null || $price === '') {
            $errors['price'] = 'price is required';
        } elseif (!is_numeric($price) || (float)$price < 0) {
            $errors['price'] = 'price must be a non-negative number';
        }

        return $errors;
    }

    public static function ensureTable(SQLite3 $db): void
    {
        $db->exec('CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
        )');
    }

    public static function insert(SQLite3 $db, array &$data): void
    {
        $stmt = $db->prepare('INSERT INTO products (name, price) VALUES (:name, :price)');
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':price', (float)$data['price'], SQLITE3_FLOAT);
        $stmt->execute();

        $id = $db->lastInsertRowID();

        $row = $db->querySingle('SELECT * FROM products WHERE id = ' . $id, true);

        $data['id'] = (int)$row['id'];
        $data['price'] = (float)$row['price'];
        $data['created_at'] = $row['created_at'];
    }

    public static function respond(int $code, array $body): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
