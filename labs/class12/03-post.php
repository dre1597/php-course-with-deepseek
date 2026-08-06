<?php
// register.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$name     = $_POST['name']     ?? '';
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
$errors = [];

if (trim($name) === '') {
    $errors[] = 'The name is required.';
}

if (trim($email) === '') {
    $errors[] = 'The email is required.';
}

if (strlen($password) < 8) {
    $errors[] = 'The password must be at least 8 characters.';
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p style='color:red'>{$error}</p>\n";
    }
    exit;
}

echo "<p>Registration successful!</p>\n";
echo "<p>Welcome, " . htmlspecialchars($name) . "!</p>\n";
