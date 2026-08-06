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
]);
