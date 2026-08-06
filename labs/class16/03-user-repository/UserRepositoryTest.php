<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/03-user-repository.php';

class UserRepositoryTest extends TestCase
{
    private ?PDO $pdo = null;
    private ?UserRepository $repository = null;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE)');
        $this->repository = new UserRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->repository = null;
    }

    public function testInsertsUser(): void
    {
        $this->repository->save('Dre');
        $result = $this->pdo->query('SELECT name FROM users')->fetch();
        $this->assertEquals('Dre', $result['name']);
    }

    public function testStartsEmpty(): void
    {
        $count = $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testFindsUserById(): void
    {
        $id = $this->repository->save('Dre');
        $user = $this->repository->findById($id);

        $this->assertNotNull($user);
        $this->assertEquals('Dre', $user['name']);
    }

    public function testCreatesUser(): void
    {
        $id = $this->repository->create('Dre', 'dre@email.com');
        $this->assertGreaterThan(0, $id);
    }

    public function testFailsOnDuplicateEmail(): void
    {
        $this->repository->create('Dre', 'dre@email.com');

        $this->expectException(\PDOException::class);
        $this->repository->create('Other', 'dre@email.com');
    }
}
