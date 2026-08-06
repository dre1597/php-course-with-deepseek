<?php
session_start();

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verifyCSRF(): void {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Invalid CSRF token. Request rejected.');
    }
}

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();

    // Process data securely...
    echo "Action executed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Protected Form</title></head>
<body>
    <form method="post">
        <?= csrfField() ?>
        <label>Name: <input type="text" name="name"></label>
        <button type="submit">Send</button>
    </form>
</body>
</html>
