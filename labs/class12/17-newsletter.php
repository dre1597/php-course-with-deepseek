<?php
// newsletter.php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    }

    // Validation
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $errors[] = 'Please provide a valid email.';
    }

    // Check if already subscribed (simulated with file)
    $subscribersFile = __DIR__ . '/newsletter.txt';
    $subscribers = file_exists($subscribersFile)
        ? file($subscribersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if (in_array($email, $subscribers)) {
        $errors[] = 'This email is already subscribed.';
    }

    if (empty($errors)) {
        file_put_contents($subscribersFile, $email . "\n", FILE_APPEND | LOCK_EX);
        $success = true;
        $email = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; display: flex;
               justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 400px; width: 100%; }
        h2 { margin-bottom: 0.5rem; }
        p.description { color: #666; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        input[type="email"] { width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 4px;
                              font-size: 1rem; }
        button { width: 100%; padding: 0.7rem; background: #2563eb; color: white; border: none;
                 border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .success { color: #16a34a; font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="card">
    <?php if ($success): ?>
        <h2>Thanks for subscribing!</h2>
        <p class="success">You'll receive our news shortly.</p>
    <?php else: ?>
        <h2>Subscribe to our Newsletter</h2>
        <p class="description">Get PHP tips straight to your inbox. No spam.</p>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="field">
                <label for="email">Your best email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>"
                       placeholder="example@email.com" required>
            </div>
            <button type="submit">Subscribe</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
