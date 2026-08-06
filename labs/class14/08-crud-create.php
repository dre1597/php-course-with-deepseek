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
    echo "Inserido ID: " . $pdo->lastInsertId() . "<br>\n";
}
