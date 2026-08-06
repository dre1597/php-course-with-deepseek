<?php
$stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$stmt->execute([':id' => 3]);

$affected = $stmt->rowCount();
echo "{$affected} linha(s) removida(s).<br>\n";
