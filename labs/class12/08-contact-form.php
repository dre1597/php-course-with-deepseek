<?php
// contact.php — processes the form on the same page
$sent = false;
$errors = [];
$name = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = 'The name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email.';
    }

    if (strlen($message) < 10) {
        $errors[] = 'The message must be at least 10 characters.';
    }

    if (empty($errors)) {
        $sent = true;
        // Here you'd save to the database or send an email
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; }
        .error { color: red; }
        .success { color: green; }
        label { display: block; margin-top: 12px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h1>Contact</h1>

    <?php if ($sent): ?>
        <p class="success">Message sent successfully! Thank you, <?= htmlspecialchars($name) ?>.</p>
    <?php else: ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="contact.php" novalidate>
            <label for="name">Name</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($name) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email) ?>">

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5"><?= htmlspecialchars($message) ?></textarea>

            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</body>
</html>
