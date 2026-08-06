<?php
// Simple redirect
header('Location: /destination-page.php');
exit; // ALWAYS call exit/die after header('Location: ...')

// Redirect with HTTP code
header('Location: /new-page.php', true, 301); // 301 = permanent
exit;

// Redirect back to previous page
$referrer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
header("Location: {$referrer}");
exit;

// Redirect with flash message (see Module 13 — Sessions)
session_start();
$_SESSION['flash_message'] = 'Operation completed successfully!';
header('Location: /index.php');
exit;
