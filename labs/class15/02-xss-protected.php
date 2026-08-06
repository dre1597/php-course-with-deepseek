<?php
// SEMPRE escape no output
$name = $_GET['name'] ?? 'Visitante';
echo "Olá, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";
// Resultado no HTML: Olá, &lt;script&gt;alert('hackeado')&lt;/script&gt;!
// O script é exibido como texto, não execut
