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
$user = $stmt->fetch();

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
// ❌ $stmt->prepare('SELECT * FROM users WHERE email = :email AND ativo = ?');
