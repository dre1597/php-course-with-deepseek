<?php
// Forçar HTTPS em produção
if (($_SERVER['HTTPS'] ?? 'off') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') != 443) {
    // Em produção, redirecione para HTTPS
    if (getenv('APP_ENV') === 'production') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit;
    }
}

// Cookies seguros
session_set_cookie_params([
    'secure'   => true,   // apenas HTTPS
    'httponly' => true,   // inacessível via JS
    'samesite' => 'Lax',
]);

// Header HSTS no PHP
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
