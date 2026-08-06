<?php
session_start();

// session_unset() — limpa todas as variáveis da sessão
// mas mantém a sessão ativa
session_unset();
echo "Variáveis limpas. A sessão continua ativa.<br>\n";

// session_destroy() — destrói a sessão do servidor
// O cookie de sessão ainda existe no navegador!
$_SESSION = []; // limpa o array

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // expira no passado
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
echo "Sessão destruída.<br>\n";

// logout.php
session_start();

// 1. Limpa os dados da sessão
$_SESSION = [];

// 2. Remove o cookie de sessão
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Redireciona para a página de login
header('Location: /login.php');
exit;
