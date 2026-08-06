<?php
// ❌ Comparação NÃO segura — vulnerável a timing attack
if ($receivedToken === $expectedToken) {
    // ...
}

// ✅ Comparação segura — tempo constante
if (hash_equals($expectedToken, $receivedToken)) {
    // ...
}

// Exemplo: verificação de token de API
function verifyAPIToken(string $receivedToken): bool {
    $actualToken = getenv('API_TOKEN');
    return hash_equals($actualToken, $receivedToken);
}

// Exemplo: verificação de CSRF
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('Token CSRF inválido.');
}
