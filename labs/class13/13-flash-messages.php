<?php
session_start();

// flash.php — Funções para flash messages

function flash(string $key, string $message = null): ?string {
    if ($message !== null) {
        // SET: guarda a mensagem
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    // GET: recupera e remove
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flashSucesso(string $message): void {
    flash('success', $message);
}

function flashErro(string $message): void {
    flash('error', $message);
}

function flashInfo(string $message): void {
    flash('info', $message);
}

// Uso:

// Em salvar.php (após process formulário)
flashSucesso('Registro salvo com sucesso!');
header('Location: /list.php');
exit;

// Em list.php (na view)
$success = flash('success');
if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php
$error = flash('error');
if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
