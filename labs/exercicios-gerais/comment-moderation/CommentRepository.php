<?php

class CommentRepository
{
    private const int SPAM_LIMIT = 3;
    private const int SPAM_WINDOW_MINUTES = 10;

    public function __construct(private readonly PDO $pdo)
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS comments (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id    INTEGER NOT NULL,
                name       TEXT    NOT NULL,
                text       TEXT    NOT NULL,
                status     TEXT    NOT NULL DEFAULT \'pending\',
                ip         TEXT    NOT NULL,
                created_at TEXT    NOT NULL DEFAULT (datetime(\'now\'))
            )
        ');
    }

    public function add(int $postId, string $name, string $text, string $ip): array
    {
        $name = trim(strip_tags($name));
        $text = trim(strip_tags($text));

        if ($name === '' || $text === '') {
            return ['success' => false, 'comment' => null, 'error' => 'name and text are required'];
        }

        if ($this->countByIpInWindow($ip) >= self::SPAM_LIMIT) {
            return ['success' => false, 'comment' => null, 'error' => 'rate_limited'];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (post_id, name, text, status, ip)
             VALUES (:post_id, :name, :text, :status, :ip)'
        );
        $stmt->execute([
            'post_id' => $postId,
            'name' => $name,
            'text' => $text,
            'status' => 'pending',
            'ip' => $ip,
        ]);

        $comment = $this->findById((int)$this->pdo->lastInsertId());

        return ['success' => true, 'comment' => $comment, 'error' => null];
    }

    public function findApproved(int $postId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM comments
             WHERE post_id = :post_id AND status = \'approved\'
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute(['post_id' => $postId]);

        return array_map($this->serialize(...), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findPending(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM comments WHERE status = \'pending\' ORDER BY created_at ASC, id ASC'
        );

        return array_map($this->serialize(...), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $comment === false ? null : $this->serialize($comment);
    }

    public function approve(int $id): void
    {
        $this->setStatus($id, 'approved');
    }

    public function reject(int $id): void
    {
        $this->setStatus($id, 'rejected');
    }

    public function countByIpInWindow(string $ip): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM comments
             WHERE ip = :ip AND created_at >= datetime('now', :window)"
        );
        $stmt->execute(['ip' => $ip, 'window' => '-' . self::SPAM_WINDOW_MINUTES . ' minutes']);

        return (int)$stmt->fetchColumn();
    }

    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE comments SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    private function serialize(array $comment): array
    {
        return [
            'id' => (int)$comment['id'],
            'post_id' => (int)$comment['post_id'],
            'name' => $comment['name'],
            'text' => $comment['text'],
            'status' => $comment['status'],
            'created_at' => $comment['created_at'],
        ];
    }
}
