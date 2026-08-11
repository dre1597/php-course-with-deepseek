<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/remember_me.php';

class RememberMeTest extends TestCase
{
    private PDO $pdo;
    private RememberMe $rememberMe;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->rememberMe = new RememberMe($this->pdo);
    }

    private function countTokensForUser(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM remember_tokens WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function testCreateTokenReturnsCorrectFormat(): void
    {
        $cookie = $this->rememberMe->createToken(42);

        $this->assertStringContainsString('42:', $cookie);
        $this->assertMatchesRegularExpression('/^\d+:[a-f0-9]{64}$/', $cookie);
    }

    public function testCreateTokenStoresHashInDatabase(): void
    {
        $this->rememberMe->createToken(7);

        $stmt = $this->pdo->query('SELECT token_hash FROM remember_tokens WHERE user_id = 7');
        $hash = $stmt->fetchColumn();

        $this->assertIsString($hash);
        $this->assertSame(64, strlen($hash));
    }

    public function testCreateTokenDoesNotStorePlainToken(): void
    {
        $cookie = $this->rememberMe->createToken(1);
        $token = explode(':', $cookie, 2)[1];

        $stmt = $this->pdo->query('SELECT token_hash FROM remember_tokens WHERE user_id = 1');
        $hash = $stmt->fetchColumn();

        $this->assertNotSame($token, $hash);
    }

    public function testValidateReturnsUserIdForValidToken(): void
    {
        $cookie = $this->rememberMe->createToken(10);
        $result = $this->rememberMe->validate($cookie);

        $this->assertSame(10, $result);
    }

    public function testValidateReturnsNullForInvalidToken(): void
    {
        $result = $this->rememberMe->validate('10:invalidtoken1234567890abcdef1234567890abcdef1234567890abcdef');

        $this->assertNull($result);
    }

    public function testValidateReturnsNullForEmptyString(): void
    {
        $result = $this->rememberMe->validate('');

        $this->assertNull($result);
    }

    public function testValidateReturnsNullForMalformedCookie(): void
    {
        $this->assertNull($this->rememberMe->validate('justsomething'));
        $this->assertNull($this->rememberMe->validate(':'));
        $this->assertNull($this->rememberMe->validate('abc:def:ghi'));
        $this->assertNull($this->rememberMe->validate('notnumeric:abc123'));
    }

    public function testTokenIsRotatedAfterSuccessfulValidation(): void
    {
        $oldCookie = $this->rememberMe->createToken(5);
        $oldToken = explode(':', $oldCookie, 2)[1];

        $this->rememberMe->validate($oldCookie);

        $stmt = $this->pdo->query('SELECT token_hash FROM remember_tokens WHERE user_id = 5');
        $newHash = $stmt->fetchColumn();

        $oldHash = hash('sha256', $oldToken);
        $this->assertNotSame($oldHash, $newHash);
        $this->assertSame(1, $this->countTokensForUser(5));
    }

    public function testOldTokenIsInvalidAfterRotation(): void
    {
        $cookie = $this->rememberMe->createToken(3);

        $this->assertSame(3, $this->rememberMe->validate($cookie));

        $result = $this->rememberMe->validate($cookie);
        $this->assertNull($result);
    }

    public function testInvalidTokenRevokesAllUserTokens(): void
    {
        $this->rememberMe->createToken(8);
        $this->rememberMe->createToken(8);
        $this->assertSame(2, $this->countTokensForUser(8));

        $this->rememberMe->validate('8:badtoken1234567890abcdef1234567890abcdef1234567890abcdef');

        $this->assertSame(0, $this->countTokensForUser(8));
    }

    public function testRevokeRemovesAllTokensForUser(): void
    {
        $this->rememberMe->createToken(20);
        $this->rememberMe->createToken(20);
        $this->rememberMe->createToken(20);

        $this->rememberMe->revoke(20);

        $this->assertSame(0, $this->countTokensForUser(20));
    }

    public function testRevokeDoesNotAffectOtherUsers(): void
    {
        $this->rememberMe->createToken(1);
        $this->rememberMe->createToken(2);
        $this->rememberMe->createToken(2);

        $this->rememberMe->revoke(2);

        $this->assertSame(1, $this->countTokensForUser(1));
        $this->assertSame(0, $this->countTokensForUser(2));
    }

    public function testRevokeAllRemovesEveryToken(): void
    {
        $this->rememberMe->createToken(1);
        $this->rememberMe->createToken(2);
        $this->rememberMe->createToken(3);

        $this->rememberMe->revokeAll();

        $count = (int) $this->pdo->query('SELECT COUNT(*) FROM remember_tokens')->fetchColumn();
        $this->assertSame(0, $count);
    }

    public function testMultipleTokensForSameUser(): void
    {
        $cookie1 = $this->rememberMe->createToken(15);
        $cookie2 = $this->rememberMe->createToken(15);
        $cookie3 = $this->rememberMe->createToken(15);

        $this->assertSame(15, $this->rememberMe->validate($cookie1));
        $this->assertSame(15, $this->rememberMe->validate($cookie2));
        $this->assertSame(15, $this->rememberMe->validate($cookie3));
    }

    public function testTokensAreRandom(): void
    {
        $cookie1 = $this->rememberMe->createToken(1);
        $cookie2 = $this->rememberMe->createToken(1);

        $this->assertNotSame($cookie1, $cookie2);
    }

    public function testUserIdIsPreservedThroughRotation(): void
    {
        $cookie = $this->rememberMe->createToken(99);
        $userId = $this->rememberMe->validate($cookie);

        $this->assertSame(99, $userId);

        $stmt = $this->pdo->query('SELECT user_id FROM remember_tokens');
        $this->assertSame(99, (int) $stmt->fetchColumn());
    }

    public function testCreateTokenWithMaxIntegerUserId(): void
    {
        $cookie = $this->rememberMe->createToken(2147483647);

        $this->assertStringStartsWith('2147483647:', $cookie);
    }

    public function testRememberTokensTableIsCreated(): void
    {
        $stmt = $this->pdo->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='remember_tokens'"
        );
        $table = $stmt->fetch();

        $this->assertNotFalse($table);
    }
}
