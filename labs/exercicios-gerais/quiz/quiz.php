<?php

require_once __DIR__ . '/Quiz.php';

session_start();
session_regenerate_id(true);

if (!isset($_SESSION['quiz']) || ($_POST['action'] ?? '') === 'restart') {
    $_SESSION['quiz'] = new Quiz();
}

$quiz = $_SESSION['quiz'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'answer') {
    $quiz->answer((int) ($_POST['option'] ?? -1));
    $_SESSION['quiz'] = $quiz;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$question = $quiz->getCurrentQuestion();
$score    = $quiz->getScore();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Quiz PHP</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; }
        .question { font-size: 1.2em; margin-bottom: 20px; }
        .options { list-style: none; padding: 0; }
        .options li { margin: 8px 0; }
        .progress { color: #666; font-size: 0.9em; margin-bottom: 20px; }
        button { padding: 8px 16px; cursor: pointer; }
    </style>
</head>
<body>

<?php if ($quiz->isFinished()): ?>
    <h1>Quiz concluído!</h1>
    <p>Você acertou <strong><?= $score['correct'] ?> de <?= $score['total'] ?></strong> perguntas.</p>
    <form method="post">
        <input type="hidden" name="action" value="restart">
        <button type="submit">Tentar novamente</button>
    </form>

<?php elseif ($question): ?>
    <p class="progress">Pergunta <?= $quiz->getCurrentQuestionIndex() + 1 ?> de <?= $score['total'] ?></p>
    <p class="question"><?= htmlspecialchars($question['question']) ?></p>

    <form method="post">
        <input type="hidden" name="action" value="answer">
        <ol class="options">
            <?php foreach ($question['options'] as $i => $option): ?>
            <li>
                <label>
                    <input type="radio" name="option" value="<?= $i ?>" required>
                    <?= htmlspecialchars($option) ?>
                </label>
            </li>
            <?php endforeach; ?>
        </ol>
        <button type="submit">Responder</button>
    </form>
<?php endif; ?>

</body>
</html>
