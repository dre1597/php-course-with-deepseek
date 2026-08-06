<?php
// Cookies definidos com setcookie só estarão disponíveis
// em $_COOKIE na PRÓXIMA requisição

// Leitura segura com operador null coalescing
$theme = $_COOKIE['theme'] ?? 'light';
$locale = $_COOKIE['locale'] ?? 'pt-BR';

// Verificar existência
if (isset($_COOKIE['remember_login'])) {
    echo "Usuário escolheu 'lembrar login'.<br>\n";
}

// Listar todos os cookies recebidos
echo "<h3>Cookies recebidos:</h3>\n";
echo "<ul>\n";
foreach ($_COOKIE as $name => $value) {
    echo "<li>" . htmlspecialchars($name) . " = " . htmlspecialchars($value) . "</li>\n";
}
echo "</ul>\n";
