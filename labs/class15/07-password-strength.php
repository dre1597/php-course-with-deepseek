<?php
function validatePasswordStrength(string $password): array {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'A password deve ter no mínimo 8 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A password deve conter ao menos uma letra maiúscula.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A password deve conter ao menos uma letra minúscula.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'A password deve conter ao menos um número.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'A password deve conter ao menos um caractere especial.';
    }

    return $errors;
}

// Uso
$password = $_POST['password'] ?? '';
$passwordErrors = validatePasswordStrength($password);
if (!empty($passwordErrors)) {
    echo "<ul>\n";
    foreach ($passwordErrors as $error) {
        echo "<li>" . h($error) . "</li>\n";
    }
    echo "</ul>\n";
}
