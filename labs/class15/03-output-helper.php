<?php
function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Uso em templates PHP puro
?>
<p>Nome: <?= h($user['name']) ?></p>
<p>Email: <?= h($user['email']) ?></p>
<p>Bio: <?= nl2br(h($user['bio'])) ?></p>
<?php

// Em atributos HTML também:
?>
<input type="text" value="<?= h($_GET['q'] ?? '') ?>">
<a href="/perfil?id=<?= h($user['id']) ?>">Perfil
