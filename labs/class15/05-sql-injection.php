<?php
// ❌ VULNERÁVEL
$sql = "SELECT * FROM users WHERE email = '{$_POST['email']}'";

// ✅ PROTEGIDO — Prepared Statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute([':email' => $_POST['email']]);

// Prepared statement até para queries dinâmicas
$order = in_array($_GET['ordem'], ['name', 'email', 'id']) ? $_GET['ordem'] : 'id';
$direction = $_GET['direcao'] === 'desc' ? 'DESC' : 'ASC';
$sql = "SELECT * FROM users ORDER BY {$order} {$direction}";
$stmt = $pdo->prepare($sql);
$stmt->execute();
