<?php
// Para remover, defina com tempo de expiração no passado
setcookie('theme', '', time() - 3600);
setcookie('locale', '', time() - 3600, '/');

// Com opções de array
setcookie('remember_login', '', [
    'expires' => time() - 3600,
    'path'    => '/',
    'secure'  => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
