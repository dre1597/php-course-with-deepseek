<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ou
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Sessão ativa<br>\n";
}
