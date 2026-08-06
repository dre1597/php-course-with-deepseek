<?php
// SEMPRE no topo do arquivo, antes de qualquer HTML
session_start();

// Agora $_SESSION está disponível
$_SESSION['user_id'] = 42;
$_SESSION['name'] = 'João Silva';
$_SESSION['logged_in_at'] = time();

echo "Sessão iniciada para {$_SESSION['name']}";
