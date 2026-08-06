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

// Define globalmente na conexão
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Agora todos os fetch() padrão retornam array associativo
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();
