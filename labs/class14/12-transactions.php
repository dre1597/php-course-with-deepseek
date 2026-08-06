<?php
try {
    $pdo->beginTransaction();

    // Insere o usuário
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Carlos', 'carlos@email.com', password_hash('password', PASSWORD_DEFAULT)]);
    $userId = $pdo->lastInsertId();

    // Cria um post para o usuário
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, titulo, conteudo) VALUES (?, ?, ?)');
    $stmt->execute([$userId, 'Primeiro Post', 'Conteúdo do primeiro post.']);

    // Se tudo deu certo, confirma (commit)
    $pdo->commit();
    echo "Usuário e post criados com sucesso!<br>\n";

} catch (Exception $e) {
    // Se algo deu errado, desfaz tudo (rollback)
    $pdo->rollBack();
    echo "Erro: " . $e->getMessage() . " — Transação revertida.<br>\n";
}

function transfer(PDO $pdo, int $sourceAccount, int $destAccount, float $amount): bool {
    try {
        $pdo->beginTransaction();

        // Verifica balance da origem
        $stmt = $pdo->prepare('SELECT balance FROM contas WHERE id = ? FOR UPDATE');
        $stmt->execute([$sourceAccount]);
        $balance = $stmt->fetchColumn();

        if ($balance === false) {
            throw new RuntimeException('Conta origem não encontrada.');
        }
        if ($balance < $amount) {
            throw new RuntimeException('Saldo insuficiente.');
        }

        // Debita da origem
        $stmt = $pdo->prepare('UPDATE contas SET balance = balance - ? WHERE id = ?');
        $stmt->execute([$amount, $sourceAccount]);

        // Credita no destino
        $stmt = $pdo->prepare('UPDATE contas SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$amount, $destAccount]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transferência falhou: " . $e->getMessage());
        return false;
    }
}
