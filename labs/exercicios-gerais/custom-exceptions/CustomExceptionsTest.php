<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/AppException.php';
require_once __DIR__ . '/ValidationException.php';
require_once __DIR__ . '/DatabaseException.php';
require_once __DIR__ . '/NotFoundException.php';

function validateUser(array $data): void
{
    if (empty($data['name'])) {
        throw new ValidationException('Name is required.', 422);
    }

    if (strlen($data['name']) < 3) {
        throw new ValidationException('Name must be at least 3 characters.', 422);
    }

    if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException('Invalid email format.', 422);
    }
}

function findUser(int $id): array
{
    $users = [
        1 => ['id' => 1, 'name' => 'Alice'],
        2 => ['id' => 2, 'name' => 'Bob'],
    ];

    if (!isset($users[$id])) {
        throw new NotFoundException("User with id {$id} not found.", 404);
    }

    return $users[$id];
}

function saveToDatabase(array $data): void
{
    if (empty($data)) {
        throw new DatabaseException('Cannot save empty data.', 500);
    }

    // simulate connection failure
    if (($data['id'] ?? 0) < 0) {
        throw new DatabaseException('Database connection failed.', 500);
    }
}

class CustomExceptionsTest extends TestCase
{
    public function testAppExceptionIsBaseClass(): void
    {
        $ex = new AppException('General error.', 100);

        $this->assertInstanceOf(\Exception::class, $ex);
        $this->assertSame('General error.', $ex->getMessage());
        $this->assertSame(100, $ex->getCode());
    }

    public function testValidationExceptionExtendsAppException(): void
    {
        $ex = new ValidationException('Invalid input.', 422);

        $this->assertInstanceOf(AppException::class, $ex);
        $this->assertInstanceOf(ValidationException::class, $ex);
        $this->assertSame('Invalid input.', $ex->getMessage());
        $this->assertSame(422, $ex->getCode());
    }

    public function testDatabaseExceptionExtendsAppException(): void
    {
        $ex = new DatabaseException('Connection timeout.', 500);

        $this->assertInstanceOf(AppException::class, $ex);
        $this->assertInstanceOf(DatabaseException::class, $ex);
        $this->assertSame('Connection timeout.', $ex->getMessage());
        $this->assertSame(500, $ex->getCode());
    }

    public function testNotFoundExceptionExtendsAppException(): void
    {
        $ex = new NotFoundException('Resource not found.', 404);

        $this->assertInstanceOf(AppException::class, $ex);
        $this->assertInstanceOf(NotFoundException::class, $ex);
        $this->assertSame('Resource not found.', $ex->getMessage());
        $this->assertSame(404, $ex->getCode());
    }

    public function testValidateUserThrowsValidationExceptionOnEmptyName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs('Name is required.');

        validateUser(['name' => '', 'email' => 'alice@email.com']);
    }

    public function testValidateUserThrowsValidationExceptionOnShortName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs('Name must be at least 3 characters.');

        validateUser(['name' => 'Al', 'email' => 'alice@email.com']);
    }

    public function testValidateUserThrowsValidationExceptionOnInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs('Invalid email format.');

        validateUser(['name' => 'Alice', 'email' => 'not-an-email']);
    }

    public function testValidateUserPassesWithValidData(): void
    {
        validateUser(['name' => 'Alice', 'email' => 'alice@email.com']);

        $this->assertTrue(true);
    }

    public function testFindUserThrowsNotFoundExceptionForNonexistentUser(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('User with id 99 not found.');

        findUser(99);
    }

    public function testFindUserReturnsUserForExistingId(): void
    {
        $user = findUser(1);

        $this->assertSame(['id' => 1, 'name' => 'Alice'], $user);
    }

    public function testSaveToDatabaseThrowsDatabaseExceptionOnEmptyData(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageIs('Cannot save empty data.');

        saveToDatabase([]);
    }

    public function testSaveToDatabaseThrowsDatabaseExceptionOnConnectionFailure(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageIs('Database connection failed.');

        saveToDatabase(['id' => -1, 'name' => 'Alice']);
    }

    public function testCatchingAppExceptionCatchesAllSubclasses(): void
    {
        $exceptions = [
            new ValidationException('validation', 422),
            new DatabaseException('database', 500),
            new NotFoundException('not found', 404),
        ];

        foreach ($exceptions as $ex) {
            $this->assertInstanceOf(AppException::class, $ex);
        }

        $this->assertCount(3, $exceptions);
    }

    public function testNestedTryCatchCatchesSpecificExceptionBeforeGeneric(): void
    {
        $log = [];

        try {
            try {
                validateUser(['name' => '', 'email' => 'a@b.com']);
            } catch (ValidationException $e) {
                $log[] = 'validation caught inner';
                throw $e;
            }
        } catch (AppException $e) {
            $log[] = 'app caught outer';
        }

        $this->assertSame(['validation caught inner', 'app caught outer'], $log);
    }

    public function testNestedTryCatchOnlyOuterCatchesWhenNoSpecificMatch(): void
    {
        $log = [];

        try {
            try {
                saveToDatabase([]);
            } catch (NotFoundException $e) {
                $log[] = 'not found caught (should not happen)';
            }
        } catch (AppException $e) {
            $log[] = 'app caught outer';
        }

        $this->assertSame(['app caught outer'], $log);
    }

    public function testFinallyExecutesWhenExceptionIsThrown(): void
    {
        $finallyRan = false;

        try {
            findUser(999);
        } catch (NotFoundException) {
            // expected
        } finally {
            $finallyRan = true;
        }

        $this->assertTrue($finallyRan);
    }

    public function testFinallyExecutesWhenNoExceptionIsThrown(): void
    {
        $finallyRan = false;

        try {
            findUser(1);
        } catch (NotFoundException) {
            // should not happen
        } finally {
            $finallyRan = true;
        }

        $this->assertTrue($finallyRan);
    }

    public function testFinallyRunsEvenWithReturnInTry(): void
    {
        $finallyRan = false;

        $result = (function () use (&$finallyRan) {
            try {
                return 'ok';
            } finally {
                $finallyRan = true;
            }
        })();

        $this->assertSame('ok', $result);
        $this->assertTrue($finallyRan);
    }

    public function testFullTryCatchFinallyWithReThrow(): void
    {
        $log = [];

        try {
            try {
                saveToDatabase(['id' => -1]);
            } catch (DatabaseException $e) {
                $log[] = 'inner: database error';
                throw $e;
            } finally {
                $log[] = 'inner: finally';
            }
        } catch (AppException $e) {
            $log[] = 'outer: ' . $e->getMessage();
        } finally {
            $log[] = 'outer: finally';
        }

        $this->assertSame([
            'inner: database error',
            'inner: finally',
            'outer: Database connection failed.',
            'outer: finally',
        ], $log);
    }
}
