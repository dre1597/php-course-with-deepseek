<?php
$stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
$stmt->execute([
    ':name'  => 'João Silva Atualizado',
    ':email' => 'joao.novo@email.com',
    ':id'    => 1,
]);

$affected = $stmt->rowCount();
echo "{$affected} linha(s) atualizada(s).<br>\n";
