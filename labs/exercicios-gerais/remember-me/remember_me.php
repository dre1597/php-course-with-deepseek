<?php

class RememberMe
{
    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS remember_tokens (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL,
                token_hash TEXT    NOT NULL,
                created_at TEXT    DEFAULT (datetime('now'))
            )
        ");
    }

    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            'INSERT INTO remember_tokens (user_id, token_hash) VALUES (:user_id, :token_hash)'
        );
        $stmt->execute(['user_id' => $userId, 'token_hash' => $hash]);

        return "{$userId}:{$token}";
    }

    public function validate(string $cookieValue): ?int
    {
        $parts = explode(':', $cookieValue, 2);

        if (count($parts) !== 2) {
            return null;
        }

        [$userId, $token] = $parts;

        if (!ctype_digit($userId)) {
            return null;
        }

        $userId = (int) $userId;
        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            'SELECT id, token_hash FROM remember_tokens WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (hash_equals($row['token_hash'], $tokenHash)) {
                $this->rotateToken($userId, $row['id']);

                return $userId;
            }
        }

        $this->revoke($userId);

        return null;
    }

    private function rotateToken(int $userId, int $oldTokenId): void
    {
        $this->pdo->prepare('DELETE FROM remember_tokens WHERE id = :id')
            ->execute(['id' => $oldTokenId]);

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $this->pdo->prepare(
            'INSERT INTO remember_tokens (user_id, token_hash) VALUES (:user_id, :token_hash)'
        )->execute(['user_id' => $userId, 'token_hash' => $hash]);
    }

    public function revoke(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM remember_tokens WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
    }

    public function revokeAll(): void
    {
        $this->pdo->exec('DELETE FROM remember_tokens');
    }
}
