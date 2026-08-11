<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/query_builder.php';

class QueryBuilderTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1,
                age INTEGER,
                created_at TEXT DEFAULT (datetime('now'))
            )
        ");

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, active, age) VALUES (:name, :email, :active, :age)'
        );

        $users = [
            ['name' => 'Alice',   'email' => 'alice@example.com',   'active' => 1, 'age' => 30],
            ['name' => 'Bob',     'email' => 'bob@example.com',     'active' => 1, 'age' => 25],
            ['name' => 'Charlie', 'email' => 'charlie@example.com', 'active' => 0, 'age' => 35],
            ['name' => 'Diana',   'email' => 'diana@example.com',   'active' => 1, 'age' => 28],
            ['name' => 'Eve',     'email' => 'eve@example.com',     'active' => 0, 'age' => 40],
        ];

        foreach ($users as $user) {
            $stmt->execute($user);
        }
    }

    private function qb(): QueryBuilder
    {
        return new QueryBuilder($this->pdo);
    }

    public function testGetReturnsAllRows(): void
    {
        $results = $this->qb()->select(['*'])->from('users')->get();

        $this->assertCount(5, $results);
    }

    public function testSelectSpecificColumns(): void
    {
        $results = $this->qb()->select(['id', 'name'])->from('users')->get();

        $this->assertCount(5, $results);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('email', $results[0]);
    }

    public function testDefaultsToAllColumnsWhenSelectNotCalled(): void
    {
        $results = $this->qb()->from('users')->get();

        $this->assertCount(5, $results);
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('email', $results[0]);
    }

    public function testWhereEquality(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('active', '=', 1)
            ->get();

        $this->assertCount(3, $results);
    }

    public function testWhereMultipleConditions(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('active', '=', 1)
            ->where('age', '>', 28)
            ->get();

        $names = array_column($results, 'name');
        $this->assertCount(1, $results);
        $this->assertContains('Alice', $names);
    }

    public function testWhereLike(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('name', 'LIKE', '%li%')
            ->get();

        $names = array_column($results, 'name');
        $this->assertCount(2, $results);
        $this->assertContains('Alice', $names);
        $this->assertContains('Charlie', $names);
    }

    public function testWhereIn(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->whereIn('id', [1, 3, 5])
            ->get();

        $names = array_column($results, 'name');
        $this->assertCount(3, $results);
        $this->assertContains('Alice', $names);
        $this->assertContains('Charlie', $names);
        $this->assertContains('Eve', $names);
    }

    public function testWhereInWithSingleElement(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->whereIn('id', [2])
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Bob', $results[0]['name']);
    }

    public function testWhereInWithEmptyArray(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->whereIn('id', [])
            ->get();

        $this->assertCount(0, $results);
    }

    public function testOrderByAscending(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('name', 'ASC')
            ->get();

        $names = array_column($results, 'name');
        $this->assertSame(['Alice', 'Bob', 'Charlie', 'Diana', 'Eve'], $names);
    }

    public function testOrderByDescending(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('name', 'DESC')
            ->get();

        $names = array_column($results, 'name');
        $this->assertSame(['Eve', 'Diana', 'Charlie', 'Bob', 'Alice'], $names);
    }

    public function testOrderByInvalidDirectionFallsBackToAsc(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('name', 'INVALID')
            ->get();

        $names = array_column($results, 'name');
        $this->assertSame(['Alice', 'Bob', 'Charlie', 'Diana', 'Eve'], $names);
    }

    public function testMultipleOrderBy(): void
    {
        $this->pdo->exec("UPDATE users SET age = 30 WHERE name = 'Alice'");

        $results = $this->qb()
            ->select(['name', 'age'])
            ->from('users')
            ->orderBy('age', 'DESC')
            ->orderBy('name', 'ASC')
            ->get();

        $this->assertSame('Eve', $results[0]['name']);
    }

    public function testLimit(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('id', 'ASC')
            ->limit(2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Bob', $results[1]['name']);
    }

    public function testOffset(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('id', 'ASC')
            ->offset(3)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Diana', $results[0]['name']);
        $this->assertSame('Eve', $results[1]['name']);
    }

    public function testLimitAndOffset(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('id', 'ASC')
            ->limit(2)
            ->offset(1)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Bob', $results[0]['name']);
        $this->assertSame('Charlie', $results[1]['name']);
    }

    public function testFirstReturnsSingleRow(): void
    {
        $result = $this->qb()
            ->select(['name'])
            ->from('users')
            ->orderBy('id', 'ASC')
            ->first();

        $this->assertIsArray($result);
        $this->assertSame('Alice', $result['name']);
    }

    public function testFirstReturnsNullWhenNoResults(): void
    {
        $result = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('id', '=', 999)
            ->first();

        $this->assertNull($result);
    }

    public function testCountReturnsAllRows(): void
    {
        $count = $this->qb()->from('users')->count();

        $this->assertSame(5, $count);
    }

    public function testCountWithWhere(): void
    {
        $count = $this->qb()
            ->from('users')
            ->where('active', '=', 1)
            ->count();

        $this->assertSame(3, $count);
    }

    public function testCountWithComplexConditions(): void
    {
        $count = $this->qb()
            ->from('users')
            ->where('active', '=', 1)
            ->where('age', '>=', 30)
            ->count();

        $this->assertSame(1, $count);
    }

    public function testInsertReturnsLastInsertId(): void
    {
        $id = $this->qb()->insert('users', [
            'name' => 'Frank',
            'email' => 'frank@example.com',
            'active' => 1,
            'age' => 22,
        ]);

        $this->assertSame('6', $id);
    }

    public function testInsertPersistsData(): void
    {
        $id = $this->qb()->insert('users', [
            'name' => 'Frank',
            'email' => 'frank@example.com',
            'active' => 1,
            'age' => 22,
        ]);

        $result = $this->qb()
            ->select(['name', 'email', 'age'])
            ->from('users')
            ->where('id', '=', (int) $id)
            ->first();

        $this->assertSame('Frank', $result['name']);
        $this->assertSame('frank@example.com', $result['email']);
        $this->assertSame(22, $result['age']);
    }

    public function testInsertMultipleRows(): void
    {
        $this->qb()->insert('users', ['name' => 'Grace', 'email' => 'grace@example.com', 'active' => 1, 'age' => 27]);
        $this->qb()->insert('users', ['name' => 'Hank',  'email' => 'hank@example.com',  'active' => 0, 'age' => 33]);

        $count = $this->qb()->from('users')->count();
        $this->assertSame(7, $count);
    }

    public function testUpdateAffectsCorrectRows(): void
    {
        $affected = $this->qb()
            ->where('active', '=', 0)
            ->update('users', ['active' => 1]);

        $this->assertSame(2, $affected);

        $count = $this->qb()
            ->from('users')
            ->where('active', '=', 1)
            ->count();

        $this->assertSame(5, $count);
    }

    public function testUpdateMultipleColumns(): void
    {
        $this->qb()
            ->where('name', '=', 'Alice')
            ->update('users', ['name' => 'Alicia', 'age' => 31]);

        $result = $this->qb()
            ->select(['name', 'age'])
            ->from('users')
            ->where('id', '=', 1)
            ->first();

        $this->assertSame('Alicia', $result['name']);
        $this->assertSame(31, $result['age']);
    }

    public function testUpdateWithoutWhereAffectsAllRows(): void
    {
        $affected = $this->qb()->update('users', ['active' => 0]);

        $this->assertSame(5, $affected);

        $count = $this->qb()
            ->from('users')
            ->where('active', '=', 1)
            ->count();

        $this->assertSame(0, $count);
    }

    public function testDeleteAffectsCorrectRows(): void
    {
        $affected = $this->qb()
            ->where('active', '=', 0)
            ->delete('users');

        $this->assertSame(2, $affected);

        $count = $this->qb()->from('users')->count();
        $this->assertSame(3, $count);
    }

    public function testDeleteWithoutWhereAffectsAllRows(): void
    {
        $affected = $this->qb()->delete('users');

        $this->assertSame(5, $affected);

        $count = $this->qb()->from('users')->count();
        $this->assertSame(0, $count);
    }

    public function testDeleteWithMultipleWhereConditions(): void
    {
        $affected = $this->qb()
            ->where('active', '=', 1)
            ->where('age', '<', 30)
            ->delete('users');

        $this->assertSame(2, $affected);

        $remaining = $this->qb()
            ->select(['name'])
            ->from('users')
            ->get();

        $names = array_column($remaining, 'name');
        $this->assertContains('Alice', $names);
        $this->assertNotContains('Diana', $names);
        $this->assertNotContains('Bob', $names);
    }

    public function testMethodsReturnSelf(): void
    {
        $qb = $this->qb();

        $this->assertSame($qb, $qb->select(['id']));
        $this->assertSame($qb, $qb->from('users'));
        $this->assertSame($qb, $qb->where('id', '=', 1));
        $this->assertSame($qb, $qb->whereIn('id', [1, 2]));
        $this->assertSame($qb, $qb->orderBy('name'));
        $this->assertSame($qb, $qb->limit(10));
        $this->assertSame($qb, $qb->offset(0));
    }

    public function testPreparedStatementsPreventSqlInjection(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('name', '=', "'; DROP TABLE users; --")
            ->get();

        $this->assertCount(0, $results);

        $this->qb()->from('users')->count();
    }

    public function testWhereWithNullValue(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('age', '=', null)
            ->get();

        $this->assertCount(0, $results);
    }

    public function testComplexQueryCombiningAllFeatures(): void
    {
        $results = $this->qb()
            ->select(['id', 'name', 'email'])
            ->from('users')
            ->where('active', '=', 1)
            ->where('age', '>=', 25)
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->offset(0)
            ->get();

        $names = array_column($results, 'name');
        $this->assertCount(3, $results);
        $this->assertSame(['Alice', 'Bob', 'Diana'], $names);
    }

    public function testMultipleQueriesWithSameBuilderAccumulateWhere(): void
    {
        $qb = $this->qb();
        $qb->select(['name'])->from('users')->where('active', '=', 1);

        $first = $qb->get();
        $this->assertCount(3, $first);

        $second = $qb->where('age', '>=', 30)->get();
        $this->assertCount(1, $second);
    }

    public function testWhereWithDifferentOperators(): void
    {
        $gteCount = $this->qb()->from('users')->where('age', '>=', 35)->count();
        $this->assertSame(2, $gteCount);

        $ltCount = $this->qb()->from('users')->where('age', '<', 30)->count();
        $this->assertSame(2, $ltCount);

        $neqCount = $this->qb()->from('users')->where('active', '!=', 1)->count();
        $this->assertSame(2, $neqCount);
    }

    public function testEmptyResultSet(): void
    {
        $results = $this->qb()
            ->select(['name'])
            ->from('users')
            ->where('id', '=', 999)
            ->get();

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testInsertWithNullValue(): void
    {
        $id = $this->qb()->insert('users', [
            'name' => 'NullAge',
            'email' => 'null@example.com',
            'active' => 1,
            'age' => null,
        ]);

        $result = $this->qb()
            ->select(['age'])
            ->from('users')
            ->where('id', '=', (int) $id)
            ->first();

        $this->assertNull($result['age']);
    }

    public function testDataTypesArePreserved(): void
    {
        $result = $this->qb()
            ->select(['*'])
            ->from('users')
            ->where('id', '=', 1)
            ->first();

        $this->assertSame(1, $result['id']);
        $this->assertSame('Alice', $result['name']);
        $this->assertSame('alice@example.com', $result['email']);
        $this->assertSame(1, $result['active']);
        $this->assertSame(30, $result['age']);
    }
}
