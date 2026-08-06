<?php
// Geração de token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Campo hidden
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// Verificação no POST
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Requisição inválida.');
}

// Renovar token após uso (opcional, aumenta segurança)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
