<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/CommentRepository.php';

class CommentRepositoryTest extends TestCase
{
    private PDO $pdo;
    private CommentRepository $comments;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->comments = new CommentRepository($this->pdo);
    }

    private function insertOldComment(string $ip, string $minutesAgo): void
    {
        $this->pdo->prepare(
            "INSERT INTO comments (post_id, name, text, status, ip, created_at)
             VALUES (1, 'old', 'old', 'approved', :ip, datetime('now', :offset))"
        )->execute(['ip' => $ip, 'offset' => '-' . $minutesAgo . ' minutes']);
    }

    public function testAddCreatesPendingComment(): void
    {
        $result = $this->comments->add(1, 'Ana', 'Ótimo post!', '192.168.0.1');

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['comment']);
        $this->assertSame('pending', $result['comment']['status']);
        $this->assertSame(1, $result['comment']['post_id']);
        $this->assertSame('Ana', $result['comment']['name']);
    }

    public function testAddWithEmptyNameFails(): void
    {
        $result = $this->comments->add(1, '   ', 'texto', '192.168.0.1');

        $this->assertFalse($result['success']);
        $this->assertNull($result['comment']);
    }

    public function testAddWithEmptyTextFails(): void
    {
        $result = $this->comments->add(1, 'Ana', '', '192.168.0.1');

        $this->assertFalse($result['success']);
    }

    public function testAddStripsHtmlTagsFromText(): void
    {
        $result = $this->comments->add(1, 'Ana', '<script>alert("xss")</script>Olá', '192.168.0.1');

        $this->assertTrue($result['success']);
        $this->assertSame('alert("xss")Olá', $result['comment']['text']);
        $this->assertStringNotContainsString('<script>', $result['comment']['text']);
    }

    public function testAddStripsHtmlTagsFromName(): void
    {
        $result = $this->comments->add(1, '<b>Ana</b>', 'texto', '192.168.0.1');

        $this->assertSame('Ana', $result['comment']['name']);
    }

    public function testAddWithOnlyTagsFails(): void
    {
        $result = $this->comments->add(1, 'Ana', '<b></b>', '192.168.0.1');

        $this->assertFalse($result['success']);
    }

    public function testPendingCommentDoesNotAppearInApproved(): void
    {
        $this->comments->add(1, 'Ana', 'aguardando moderação', '192.168.0.1');

        $approved = $this->comments->findApproved(1);

        $this->assertEmpty($approved);
    }

    public function testApproveMakesCommentVisible(): void
    {
        $result = $this->comments->add(1, 'Ana', 'aprovado depois', '192.168.0.1');
        $this->comments->approve($result['comment']['id']);

        $approved = $this->comments->findApproved(1);

        $this->assertCount(1, $approved);
        $this->assertSame('approved', $approved[0]['status']);
    }

    public function testRejectKeepsCommentHidden(): void
    {
        $result = $this->comments->add(1, 'Ana', 'vai ser rejeitado', '192.168.0.1');
        $this->comments->reject($result['comment']['id']);

        $approved = $this->comments->findApproved(1);
        $pending = $this->comments->findPending();

        $this->assertEmpty($approved);
        $this->assertEmpty($pending);
    }

    public function testRejectedCommentIsMarkedAsRejected(): void
    {
        $result = $this->comments->add(1, 'Ana', 'rejeitado', '192.168.0.1');
        $this->comments->reject($result['comment']['id']);

        $comment = $this->comments->findById($result['comment']['id']);

        $this->assertSame('rejected', $comment['status']);
    }

    public function testFindPendingListsOnlyPending(): void
    {
        $first = $this->comments->add(1, 'A', 'um', '10.0.0.1');
        $second = $this->comments->add(1, 'B', 'dois', '10.0.0.2');

        $this->comments->approve($first['comment']['id']);

        $pending = $this->comments->findPending();

        $this->assertCount(1, $pending);
        $this->assertSame($second['comment']['id'], $pending[0]['id']);
    }

    public function testFindApprovedOrdersByDateAscending(): void
    {
        $this->insertOldComment('10.0.0.1', '5');
        $this->insertOldComment('10.0.0.2', '1');

        $approved = $this->comments->findApproved(1);

        $this->assertCount(2, $approved);
        $this->assertLessThan($approved[1]['created_at'], $approved[0]['created_at']);
    }

    public function testFindApprovedFiltersByPost(): void
    {
        $first = $this->comments->add(1, 'A', 'post 1', '10.0.0.1');
        $second = $this->comments->add(2, 'B', 'post 2', '10.0.0.2');

        $this->comments->approve($first['comment']['id']);
        $this->comments->approve($second['comment']['id']);

        $postOneComments = $this->comments->findApproved(1);
        $postTwoComments = $this->comments->findApproved(2);

        $this->assertCount(1, $postOneComments);
        $this->assertSame('post 1', $postOneComments[0]['text']);
        $this->assertCount(1, $postTwoComments);
        $this->assertSame('post 2', $postTwoComments[0]['text']);
    }

    public function testFindByIdReturnsNullForMissingComment(): void
    {
        $this->assertNull($this->comments->findById(999));
    }

    public function testApproveMissingCommentDoesNotThrow(): void
    {
        $this->comments->approve(999);

        $this->expectNotToPerformAssertions();
    }

    public function testRateLimitAllowsThreeCommentsPerIp(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $result = $this->comments->add(1, 'Ana', 'comentário ' . $i, '200.0.0.1');
            $this->assertTrue($result['success']);
        }

        $fourth = $this->comments->add(1, 'Ana', 'quarto', '200.0.0.1');

        $this->assertFalse($fourth['success']);
        $this->assertSame('rate_limited', $fourth['error']);
    }

    public function testRateLimitIsPerIp(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->comments->add(1, 'Ana', 'x', '200.0.0.1');
        }

        $otherIp = $this->comments->add(1, 'Bia', 'y', '200.0.0.2');

        $this->assertTrue($otherIp['success']);
    }

    public function testRateLimitExpiresAfterWindow(): void
    {
        $this->insertOldComment('200.0.0.1', '11');
        $this->insertOldComment('200.0.0.1', '12');
        $this->insertOldComment('200.0.0.1', '13');

        $this->assertSame(0, $this->comments->countByIpInWindow('200.0.0.1'));

        $result = $this->comments->add(1, 'Ana', 'após expirar', '200.0.0.1');

        $this->assertTrue($result['success']);
    }

    public function testCountByIpInWindowCountsRecentComments(): void
    {
        $this->comments->add(1, 'Ana', 'um', '200.0.0.1');
        $this->comments->add(1, 'Ana', 'dois', '200.0.0.1');

        $this->assertSame(2, $this->comments->countByIpInWindow('200.0.0.1'));
    }

    public function testEscapeOutputsHtmlEntities(): void
    {
        $escaped = CommentRepository::escape('<script>alert("x")</script>');

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testEscapeHandlesQuotes(): void
    {
        $escaped = CommentRepository::escape('"aspas" e \'simples\'');

        $this->assertStringContainsString('&quot;', $escaped);
        $this->assertStringContainsString('&apos;', $escaped);
    }
}
