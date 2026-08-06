<?php
session_start();

// Após login bem-sucedido
$_SESSION['user_id'] = $user['id'];

// Regenera o ID — importante para segurança!
// true: remove o arquivo de sessão antigo
session_regenerate_id(true);

echo "Login realizado. ID da sessão foi regenerado.<br>\n";
