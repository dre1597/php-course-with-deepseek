<?php
// Cookies armazenam strings. Para guardar arrays, serialize ou json_encode.

// Guardar preferências como JSON
$preferences = ['theme' => 'dark', 'font' => 'large', 'notifications' => false];
setcookie('prefs', json_encode($preferences), time() + (86400 * 365), '/');

$prefs = json_decode($_COOKIE['prefs'] ?? '{}', true);
echo "Tema: " . ($prefs['theme'] ?? 'light') . "<br>\n";

$visits = (int) ($_COOKIE['visits'] ?? 0);
$visits++;
setcookie('visits', (string) $visits, time() + (86400 * 365), '/');
echo "Você visitou esta página {$visits} vez(es).<br>\n";
