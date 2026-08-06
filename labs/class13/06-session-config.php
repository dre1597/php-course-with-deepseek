<?php
// Configurar antes de session_start()

// Tempo de vida da sessão no servidor (em segundos)
// 3600 = 1 hora, 86400 = 24 horas
ini_set('session.gc_maxlifetime', 86400); // 24 horas

// Cookies de sessão
ini_set('session.cookie_lifetime', 0);      // 0 = até fechar navegador
ini_set('session.cookie_path', '/');         // disponível em todo site
ini_set('session.cookie_domain', '');        // domínio atual
ini_set('session.cookie_secure', '1');       // apenas HTTPS
ini_set('session.cookie_httponly', '1');     // inacessível via JavaScript
ini_set('session.cookie_samesite', 'Lax');   // proteção CSRF

// Nome do cookie de sessão (mudar do padrão PHPSESSID)
session_name('MEUAPP_SESSID');

// Diretório onde os arquivos de sessão são salvos
// session.save_path — não pode ser alterado via ini_set em produção
// Configurar no php.ini

// Probabilidade de coleta de lixo (garbage collection)
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100); // 1% de chance a cada requisição

session_start();

// Alternativa mais limpa que ini_set
session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'domain'   => 'meusite.com.br',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();
