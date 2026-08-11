<?php

require_once __DIR__ . '/theme.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newTheme = toggleTheme();
  applyTheme($newTheme);
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

$currentTheme = getCurrentTheme();
$bodyClass = $currentTheme === THEME_DARK ? 'dark-theme' : 'light-theme';
?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>Preferência de Tema</title>
    <style>
        body.light-theme {
            background: #fff;
            color: #111;
        }

        body.dark-theme {
            background: #1a1a2e;
            color: #eee;
        }

        .container {
            max-width: 400px;
            margin: 80px auto;
            text-align: center;
        }

        button {
            padding: 10px 24px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
  </head>
  <body class="<?= $bodyClass ?>">
    <div class="container">
      <h1>Tema atual: <?= $currentTheme === THEME_DARK ? 'Escuro' : 'Claro' ?></h1>
      <form method="post">
        <button type="submit">
          Alternar para tema <?= $currentTheme === THEME_DARK ? 'Claro' : 'Escuro' ?>
        </button>
      </form>
    </div>
  </body>
</html>
