<?php

require_once __DIR__ . '/FormWizard.php';

$step   = FormWizard::getStep($_POST);
$errors = [];

if ($step > 1) {
    $errors = FormWizard::validateStep($step - 1, $_POST);
    if (!empty($errors)) {
        $step = $step - 1;
    }
}

$data = FormWizard::allFields($_POST);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastro — Etapa <?= $step ?> de 3</title>
</head>
<body>

<?php if ($step === 4): ?>
    <h1>Cadastro concluído!</h1>
    <p><strong>Nome:</strong> <?= htmlspecialchars($data['name']) ?></p>
    <p><strong>E-mail:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Nascimento:</strong> <?= htmlspecialchars($data['birth_date']) ?></p>
    <p><strong>Endereço:</strong> <?= htmlspecialchars($data['street']) ?>, <?= htmlspecialchars($data['number']) ?></p>
    <p><strong>Cidade/Estado:</strong> <?= htmlspecialchars($data['city']) ?> / <?= htmlspecialchars($data['state']) ?></p>
    <p><a href="?">Novo cadastro</a></p>

<?php elseif ($step === 1): ?>
    <h1>Etapa 1 — Dados Pessoais</h1>
    <form method="post">
        <?= FormWizard::renderHiddenFields($_POST, 1) ?>
        <input type="hidden" name="_step" value="2">

        <label>Nome:
            <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>">
            <?php if (isset($errors['name'])): ?><em><?= $errors['name'] ?></em><?php endif; ?>
        </label><br>

        <label>E-mail:
            <input type="text" name="email" value="<?= htmlspecialchars($data['email']) ?>">
            <?php if (isset($errors['email'])): ?><em><?= $errors['email'] ?></em><?php endif; ?>
        </label><br>

        <label>Data de nascimento:
            <input type="date" name="birth_date" value="<?= htmlspecialchars($data['birth_date']) ?>">
            <?php if (isset($errors['birth_date'])): ?><em><?= $errors['birth_date'] ?></em><?php endif; ?>
        </label><br>

        <button type="submit">Próximo</button>
    </form>

<?php elseif ($step === 2): ?>
    <h1>Etapa 2 — Endereço</h1>
    <form method="post">
        <?= FormWizard::renderHiddenFields($_POST, 2) ?>
        <input type="hidden" name="_step" value="3">

        <label>Rua:
            <input type="text" name="street" value="<?= htmlspecialchars($data['street']) ?>">
            <?php if (isset($errors['street'])): ?><em><?= $errors['street'] ?></em><?php endif; ?>
        </label><br>

        <label>Número:
            <input type="text" name="number" value="<?= htmlspecialchars($data['number']) ?>">
            <?php if (isset($errors['number'])): ?><em><?= $errors['number'] ?></em><?php endif; ?>
        </label><br>

        <label>Cidade:
            <input type="text" name="city" value="<?= htmlspecialchars($data['city']) ?>">
            <?php if (isset($errors['city'])): ?><em><?= $errors['city'] ?></em><?php endif; ?>
        </label><br>

        <label>Estado:
            <input type="text" name="state" value="<?= htmlspecialchars($data['state']) ?>">
            <?php if (isset($errors['state'])): ?><em><?= $errors['state'] ?></em><?php endif; ?>
        </label><br>

        <button type="submit">Próximo</button>
    </form>

<?php else: ?>
    <h1>Etapa 3 — Confirmação</h1>
    <p><strong>Nome:</strong> <?= htmlspecialchars($data['name']) ?></p>
    <p><strong>E-mail:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Nascimento:</strong> <?= htmlspecialchars($data['birth_date']) ?></p>
    <p><strong>Rua:</strong> <?= htmlspecialchars($data['street']) ?>, <?= htmlspecialchars($data['number']) ?></p>
    <p><strong>Cidade/Estado:</strong> <?= htmlspecialchars($data['city']) ?> / <?= htmlspecialchars($data['state']) ?></p>

    <form method="post">
        <?= FormWizard::renderHiddenFields($_POST, 3) ?>
        <input type="hidden" name="_step" value="4">
        <button type="submit">Confirmar</button>
    </form>
<?php endif; ?>

</body>
</html>
