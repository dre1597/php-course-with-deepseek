<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/schema_migration.php';

class SchemaMigrationTest extends TestCase
{
    private PDO $pdo;
    private string $migrationsDir;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->migrationsDir = sys_get_temp_dir() . '/migration_test_' . uniqid();
        mkdir($this->migrationsDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->migrationsDir);
    }

    private function createMigrationFile(string $filename, string $sql): void
    {
        file_put_contents(
            $this->migrationsDir . '/' . $filename,
            $sql . "\n"
        );
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function makeSchemaMigration(): SchemaMigration
    {
        return new SchemaMigration($this->pdo, $this->migrationsDir);
    }

    private function assertTableExists(string $tableName): void
    {
        $stmt = $this->pdo->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'"
        );
        $this->assertNotFalse($stmt->fetch(), "Table '$tableName' should exist.");
    }

    public function testMigrateWithNoMigrationsReturnsEmptyArray(): void
    {
        $migration = $this->makeSchemaMigration();

        $result = $migration->migrate();

        $this->assertSame([], $result);
    }

    public function testMigrateExecutesPendingMigrations(): void
    {
        $this->createMigrationFile('001_create_users.sql', 'CREATE TABLE users (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_create_users.sql'], $result);
        $this->assertTableExists('users');
    }

    public function testMigrateExecutesMultipleMigrationsInOrder(): void
    {
        $this->createMigrationFile('001_create_users.sql', 'CREATE TABLE users (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('002_add_email.sql', 'ALTER TABLE users ADD COLUMN email TEXT;');
        $this->createMigrationFile('003_create_posts.sql', 'CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER);');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $expected = ['001_create_users.sql', '002_add_email.sql', '003_create_posts.sql'];
        $this->assertSame($expected, $result);
        $this->assertTableExists('users');
        $this->assertTableExists('posts');
    }

    public function testMigrateOnlyExecutesNewMigrationsOnSecondRun(): void
    {
        $this->createMigrationFile('001_create_users.sql', 'CREATE TABLE users (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('002_create_posts.sql', 'CREATE TABLE posts (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();

        $first = $migration->migrate();
        $this->assertSame(['001_create_users.sql', '002_create_posts.sql'], $first);

        $this->createMigrationFile('003_add_profile.sql', 'CREATE TABLE profile (id INTEGER PRIMARY KEY);');

        $second = $migration->migrate();
        $this->assertSame(['003_add_profile.sql'], $second);
        $this->assertTableExists('profile');
    }

    public function testMigrateIsIdempotent(): void
    {
        $this->createMigrationFile('001_create_users.sql', 'CREATE TABLE users (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();

        $migration->migrate();
        $second = $migration->migrate();

        $this->assertSame([], $second);
    }

    public function testGetExecutedReturnsEmptyInitially(): void
    {
        $migration = $this->makeSchemaMigration();

        $this->assertSame([], $migration->getExecuted());
    }

    public function testGetExecutedReturnsAllExecutedAfterMigrate(): void
    {
        $this->createMigrationFile('001_a.sql', 'SELECT 1;');
        $this->createMigrationFile('002_b.sql', 'SELECT 1;');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $executed = $migration->getExecuted();
        $this->assertSame(['001_a.sql', '002_b.sql'], $executed);
    }

    public function testGetPendingReturnsAllWhenNoneExecuted(): void
    {
        $this->createMigrationFile('001_a.sql', 'SELECT 1;');
        $this->createMigrationFile('002_b.sql', 'SELECT 1;');

        $migration = $this->makeSchemaMigration();

        $pending = $migration->getPending();
        $this->assertSame(['001_a.sql', '002_b.sql'], $pending);
    }

    public function testGetPendingReturnsRemainingAfterPartialExecution(): void
    {
        $this->createMigrationFile('001_a.sql', 'SELECT 1;');
        $this->createMigrationFile('002_b.sql', 'SELECT 1;');
        $this->createMigrationFile('003_c.sql', 'SELECT 1;');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $this->createMigrationFile('004_d.sql', 'SELECT 1;');

        $pending = $migration->getPending();
        $this->assertSame(['004_d.sql'], $pending);
    }

    public function testGetPendingReturnsEmptyWhenAllExecuted(): void
    {
        $this->createMigrationFile('001_a.sql', 'SELECT 1;');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $this->assertSame([], $migration->getPending());
    }

    public function testMigrationsAreExecutedInAlphabeticalOrder(): void
    {
        $this->createMigrationFile('010_z.sql', 'CREATE TABLE z_table (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('001_y.sql', 'CREATE TABLE y_table (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('003_x.sql', 'CREATE TABLE x_table (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_y.sql', '003_x.sql', '010_z.sql'], $result);
    }

    public function testMigrationsAreSortedNaturallyByFilename(): void
    {
        $files = [
            '001',
            '002',
            '010',
            '020',
        ];

        foreach ($files as $num) {
            $this->createMigrationFile("{$num}_migration.sql", "CREATE TABLE t$num (id INTEGER PRIMARY KEY);");
        }

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        foreach ($files as $num) {
            $this->assertTableExists("t$num");
        }
    }

    public function testInvalidSqlThrowsException(): void
    {
        $this->createMigrationFile('001_broken.sql', 'INVALID SQL SYNTAX YOLO;');

        $migration = $this->makeSchemaMigration();

        $this->expectException(PDOException::class);
        $migration->migrate();
    }

    public function testFailedMigrationIsNotMarkedAsExecuted(): void
    {
        $this->createMigrationFile('001_good.sql', 'CREATE TABLE good (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('002_broken.sql', 'INVALID SQL SYNTAX YOLO;');

        $migration = $this->makeSchemaMigration();

        try {
            $migration->migrate();
        } catch (PDOException) {
        }

        $executed = $migration->getExecuted();
        $this->assertSame(['001_good.sql'], $executed, 'Broken migration should not be marked as executed.');
        $this->assertTrue(in_array('002_broken.sql', $migration->getPending(), true));
    }

    public function testGoodMigrationsAfterFailedAreNotExecuted(): void
    {
        $this->createMigrationFile('001_good.sql', 'CREATE TABLE good (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('002_broken.sql', 'INVALID SQL SYNTAX YOLO;');
        $this->createMigrationFile('003_also_good.sql', 'CREATE TABLE also_good (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();

        try {
            $migration->migrate();
        } catch (PDOException) {
        }

        $this->assertTableExists('good');
        $this->assertNotTrue(
            $this->tableExistsAfterFailure('also_good'),
            'Migration after a failed one should not execute.'
        );
    }

    private function tableExistsAfterFailure(string $tableName): bool
    {
        $stmt = $this->pdo->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'"
        );
        return $stmt->fetch() !== false;
    }

    public function testEmptySqlFile(): void
    {
        $this->createMigrationFile('001_empty.sql', '');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_empty.sql'], $result);
    }

    public function testSqlFileWithOnlyComments(): void
    {
        $this->createMigrationFile('001_comments.sql', "-- this is a comment\n-- another comment");

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_comments.sql'], $result);
    }

    public function testSqlFileWithMultipleStatements(): void
    {
        $sql = "CREATE TABLE a (id INTEGER PRIMARY KEY);\nCREATE TABLE b (id INTEGER PRIMARY KEY);";

        $this->createMigrationFile('001_multi.sql', $sql);

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_multi.sql'], $result);
        $this->assertTableExists('a');
        $this->assertTableExists('b');
    }

    public function testDirectoryWithNonSqlFiles(): void
    {
        $this->createMigrationFile('001_real.sql', 'CREATE TABLE real_table (id INTEGER PRIMARY KEY);');
        file_put_contents($this->migrationsDir . '/README.txt', 'ignore me please');
        file_put_contents($this->migrationsDir . '/.hidden', 'also ignore');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_real.sql'], $result);
        $this->assertTableExists('real_table');
    }

    public function testNoSqlFilesReturnsEmpty(): void
    {
        file_put_contents($this->migrationsDir . '/readme.txt', 'no sql here');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame([], $result);
    }

    public function testExecuteAfterRescanningDirectory(): void
    {
        $this->createMigrationFile('001_one.sql', 'CREATE TABLE one (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $this->createMigrationFile('002_two.sql', 'CREATE TABLE two (id INTEGER PRIMARY KEY);');

        $pending = $migration->getPending();
        $this->assertSame(['002_two.sql'], $pending);

        $migration->migrate();
        $this->assertTableExists('two');
        $this->assertSame([], $migration->getPending());
    }

    public function testManySequentialMigrations(): void
    {
        $count = 50;
        for ($i = 1; $i <= $count; $i++) {
            $number = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $this->createMigrationFile(
                "{$number}_create_t$i.sql",
                "CREATE TABLE t$i (id INTEGER PRIMARY KEY);"
            );
        }

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertCount($count, $result);
        $this->assertTableExists('t1');
        $this->assertTableExists("t$count");
    }

    public function testMigrationsTableIsCreatedOnConstruct(): void
    {
        $this->createMigrationFile('001_m.sql', 'CREATE TABLE m (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $this->assertTableExists('migrations');
    }

    public function testMigrationFilesWithLeadingZeros(): void
    {
        $this->createMigrationFile('001_first.sql', 'CREATE TABLE first (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('009_mid.sql', 'CREATE TABLE mid (id INTEGER PRIMARY KEY);');
        $this->createMigrationFile('100_last.sql', 'CREATE TABLE last (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $result = $migration->migrate();

        $this->assertSame(['001_first.sql', '009_mid.sql', '100_last.sql'], $result);
    }

    public function testMigrationExecutedAtIsRecorded(): void
    {
        $this->createMigrationFile('001_a.sql', 'SELECT 1;');
        $this->createMigrationFile('002_b.sql', 'SELECT 1;');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $stmt = $this->pdo->query('SELECT name, executed_at FROM migrations ORDER BY id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);
        $this->assertSame('001_a.sql', $rows[0]['name']);
        $this->assertSame('002_b.sql', $rows[1]['name']);
        $this->assertNotEmpty($rows[0]['executed_at']);
        $this->assertNotEmpty($rows[1]['executed_at']);
    }

    public function testSecondRunHasDifferentExecutedAt(): void
    {
        $this->createMigrationFile('001_a.sql', 'CREATE TABLE aaa (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();

        $stmt = $this->pdo->query("SELECT executed_at FROM migrations WHERE name = '001_a.sql'");
        $firstExecutedAt = $stmt->fetchColumn();

        $this->createMigrationFile('002_b.sql', 'CREATE TABLE bbb (id INTEGER PRIMARY KEY);');
        $migration->migrate();

        $stmt = $this->pdo->query("SELECT executed_at FROM migrations WHERE name = '001_a.sql'");
        $stillTheSame = $stmt->fetchColumn();

        $this->assertSame($firstExecutedAt, $stillTheSame);
    }

    public function testMigrationNameIsUniqueInDatabase(): void
    {
        $this->createMigrationFile('001_users.sql', 'CREATE TABLE users (id INTEGER PRIMARY KEY);');

        $migration = $this->makeSchemaMigration();
        $migration->migrate();
        $migration->migrate();

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM migrations WHERE name = '001_users.sql'");
        $count = (int)$stmt->fetchColumn();

        $this->assertSame(1, $count);
    }
}
