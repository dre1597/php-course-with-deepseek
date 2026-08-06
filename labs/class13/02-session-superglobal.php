<?php
session_start();

// Guardando dados
$_SESSION['usuario'] = [
    'id'    => 1,
    'name'  => 'João',
    'email' => 'joao@email.com',
    'role'  => 'admin',
];

// Guardar preferências
$_SESSION['theme'] = 'dark';
$_SESSION['cart'] = [
    ['product_id' => 10, 'quantity' => 2],
    ['product_id' => 15, 'quantity' => 1],
];

// Recuperando dados
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    echo "Bem-vindo, {$user['name']}!<br>\n";
    echo "Função: {$user['role']}<br>\n";
}

// Operações com session
$totalItems = count($_SESSION['cart']);

// Remover um item específico
unset($_SESSION['cart'][0]);

// Adicionar ao carrinho
$_SESSION['cart'][] = ['product_id' => 20, 'quantity' => 1];
