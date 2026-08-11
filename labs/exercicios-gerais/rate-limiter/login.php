<?php

require_once __DIR__ . '/RateLimiter.php';

session_start();

$rateLimiter = new RateLimiter(__DIR__ . '/.rate-data');
$remaining = $rateLimiter->getRemaining();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$rateLimiter->attempt()) {
    $retry = $rateLimiter->getRetryAfter();
    $minutes = ceil($retry / 60);
    $error = "Muitas tentativas! Tente novamente em $minutes minuto(s).";
  } else {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin') {
      $rateLimiter->reset();
      $_SESSION['user'] = $username;
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit;
    }

    $remaining = $rateLimiter->getRemaining();
    $error = "Credenciais inválidas. Tentativas restantes — Sessão: {$remaining['session']}, IP: {$remaining['ip']}";
  }
}

if (($_GET['action'] ?? '') === 'reset') {
  $rateLimiter->reset();
  session_destroy();
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>Rate Limiter — Login</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 400px;
            margin: 60px auto;
            text-align: center;
        }

        form {
            text-align: left;
        }

        label {
            display: block;
            margin: 10px 0 4px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            margin-top: 16px;
            padding: 10px 24px;
            cursor: pointer;
        }

        .error {
            color: #c00;
            background: #fdd;
            padding: 10px;
            border-radius: 4px;
            margin: 16px 0;
        }

        .success {
            color: #060;
            background: #dfd;
            padding: 10px;
            border-radius: 4px;
            margin: 16px 0;
        }

        .info {
            color: #555;
            font-size: 0.9em;
            margin-top: 20px;
        }
    </style>
  </head>
  <body>

    <?php if (isset($_SESSION['user'])): ?>
      <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['user']) ?>!</h1>
      <p class="success">Login realizado com sucesso.</p>
      <p><a href="?action=reset">Sair e resetar limites</a></p>

    <?php else: ?>
      <h1>Login</h1>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Entrar</button>
      </form>

      <p class="info">
        Tentativas restantes: <strong><?= $remaining['session'] ?></strong> (sessão) /
        <strong><?= $remaining['ip'] ?></strong> (IP)<br>
        Limites: 5/sessão e 20/IP a cada 15 minutos.<br>
        <small>Use admin / admin para testar.</small>
      </p>
    <?php endif; ?>

  </body>
</html>
