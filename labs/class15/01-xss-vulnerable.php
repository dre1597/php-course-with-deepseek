<?php
// NUNCA faça isso — o HTML do usuário é renderizado como código
$name = $_GET['name'] ?? 'Visitante';
echo "Olá, {$name}!";
// URL: pagina.php?name=<script>alert('hackeado')</script>
// Resultado: o script é execut
