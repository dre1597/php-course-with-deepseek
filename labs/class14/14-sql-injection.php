<?php
// VULNERÁVEL — NUNCA faça:
$email = $_POST['email'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE email = '{$email}' AND password = '{$password}'";

// Se o atacante digitar no campo email:
// ' OR 1=1 --
// O SQL se torna:
// SELECT * FROM users WHERE email = '' OR 1=1 --' AND password = 'qualquer'
// Isso retorna TODOS os usuários!

// Se o atacante digitar no campo email:
// '; DROP TABLE users; --
// O SQL se torna:
// SELECT * FROM users WHERE email = ''; DROP TABLE users; --' AND password = ''
// A tabela é DELETADA!

// PROTEGIDO — Prepared Statement:
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND password = :password');
$stmt->execute([':email' => $email, ':password' => $password]);

// O SGBD trata email e password como VALORES LITERAIS, nunca como código SQL.
// Mesmo que o atacante digite ' OR 1=1 --, isso será tratado como uma
// string literal e não como SQL executável.
