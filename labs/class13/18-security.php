<?php
session_start();

// 1. Habilitar strict mode (php.ini)
// session.use_strict_mode = On

// 2. Regenerar ID após login
session_regenerate_id(true);

// 1. Vincular sessão ao IP e User-Agent
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Sessão potencialmente roubada — força logout
    session_destroy();
    header('Location: /login.php?error=session');
    exit;
}

// 2. Cookies HttpOnly (não acessível via JavaScript)
ini_set('session.cookie_httponly', '1');

// 3. Cookies Secure (apenas HTTPS)
ini_set('session.cookie_secure', '1');

// 4. SameSite=Strict para prevenir CSRF
ini_set('session.cookie_samesite', 'Strict');
