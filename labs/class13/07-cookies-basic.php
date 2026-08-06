<?php
// setcookie(name, valor, expira, path, domain, secure, httponly, samesite)

// Cookie simples
setcookie('theme', 'dark');

// Cookie com tempo de expiração
// time() + segundos
setcookie('remember_login', 'sim', time() + (86400 * 30)); // 30 dias
setcookie('locale', 'pt-BR', time() + (86400 * 365)); // 1 ano

// Cookie com caminho específico (só disponível em /admin)
setcookie('admin_token', 'abc123', time() + 3600, '/admin');

// Cookie com todas as opções de segurança
setcookie(
    'token',
    'valor-codificado',
    [
        'expires'  => time() + 86400,
        'path'     => '/',
        'domain'   => '',               // domínio atual
        'secure'   => true,             // apenas HTTPS
        'httponly' => true,             // não acessível via JavaScript
        'samesite' => 'Strict',          // Lax, Strict ou None
    ]
);
