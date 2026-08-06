<?php
// Cookies SameSite já ajudam na proteção CSRF
setcookie('session', 'value', [
    'samesite' => 'Strict', // ou Lax
]);

// Strict: cookie nunca enviado em requisições cross-site
// Lax: cookie enviado apenas em GET cross-site (ex: clique em link)
// None: sempre enviado (requer Sec
