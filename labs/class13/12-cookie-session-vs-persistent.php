<?php
// Cookie de sessão: definido SEM expires ou com lifetime 0
// Desaparece quando o navegador fecha
setcookie('visited_page', '1', 0);
setcookie('visited_page', '1', ['expires' => 0]);

// Cookie persistente: tem tempo de expiração definido
// Sobrevive ao fechamento do navegador
setcookie('remember_user', 'joao', time() + (86400 * 30)); // 30 dias
