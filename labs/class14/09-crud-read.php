<?php
// Busca por ID
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => 1]);
$user = $stmt->fetch();

if ($user) {
    echo "Nome: {$user['name']}<br>\n";
    echo "Email: {$user['email']}<br>\n";
} else {
    echo "Usuário não encontrado.<br>\n";
}

// Busca com LIKE
$term = '%joão%';
$stmt = $pdo->prepare('SELECT * FROM users WHERE name LIKE :term OR email LIKE :term2');
$stmt->execute([':term' => $term, ':term2' => $term]);

// Busca com ORDER BY e LIMIT
$stmt = $pdo->prepare('SELECT * FROM users ORDER BY name ASC LIMIT :limite');
$stmt->bindValue(':limite', 10, PDO::PARAM_INT);
$stmt->execute();

// Busca condicional
$stmt = $pdo->prepare('SELECT * FROM users WHERE ativo = :ativo');
$stmt->bindValue(':ativo', 1, PDO::PARAM_BOOL);
$stmt->execute();
