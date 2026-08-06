<?php
// login.php
session_start();

$error = '';
$email = '';

// Usuários hardcoded para demonstração
$users = [
    'admin@email.com' => [
        'name'  => 'Administrador',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'id'    => 1,
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Preencha todos os campos.';
    } elseif (isset($users[$email])) {
        $user = $users[$email];

        if (password_verify($password, $user['password'])) {
            // Login bem-sucedido!
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $email,
            ];

            session_regenerate_id(true); // previne session fixation

            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Email ou password incorretos.';
        }
    } else {
        // Use mensagem genérica para não revelar se o email existe
        $error = 'Email ou password incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Login</title>
<style>
    body { font-family: sans-serif; max-width: 380px; margin: 60px auto; }
    .erro { background: #fee; color: #c00; padding: 10px; border-radius: 4px; }
    label { display: block; margin: 12px 0 4px; font-weight: 600; }
    input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { margin-top: 16px; padding: 10px 24px; background: #2563eb; color: white;
             border: none; border-radius: 4px; cursor: pointer; }
</style></head>
<body>
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="erro"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
