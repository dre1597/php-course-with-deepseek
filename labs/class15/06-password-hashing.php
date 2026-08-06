<?php
// ==================== CADASTRO ====================
$plainPassword = $_POST['password'] ?? '';

// password_hash usa bcrypt por padrão (PHP 8.4+: custo padrão mudou de 10 para 12!)
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);
echo "Hash gerado: {$hash}<br>\n";
// Exemplo: $2y$12$eMqBv... (bcrypt, custo 12)

// Opções personalizadas
$options = [
    'cost' => 12,            // PHP 8.4+: custo padrão subiu de 10 para 12
];
$customHash = password_hash($plainPassword, PASSWORD_BCRYPT, $options);

// Usando Argon2id (requer PHP compilado com suporte)
if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon = password_hash($plainPassword, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64 MB
        'time_cost'   => 4,
        'threads'     => 3,
    ]);
    echo "Hash Argon2id: {$hashArgon}<br>\n";
}

// ==================== LOGIN ====================
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Senha correta!
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    session_regenerate_id(true);
    header('Location: /dashboard.php');
    exit;
} else {
    // Mensagem genérica — não revele se o email existe
    echo "Email ou password incorretos.";
}

// Verificar se o hash precisa ser re-gerado (algoritmo ou custo mudou)
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute([':password' => $newHash, ':id' => $user['id']]);
    echo "Hash atualizado.<br>\n";
}

