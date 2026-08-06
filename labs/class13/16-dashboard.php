<?php
// dashboard.php
session_start();

// Verifica se está logado
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($user['name']) ?>!</h1>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>ID: <?= $user['id'] ?></p>
    <a href="/logout.php">Sair</a>
</body>
</html>
