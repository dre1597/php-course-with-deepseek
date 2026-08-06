# Module 16: Automated Testing with PHPUnit

## Overview

Automated testing means writing code that verifies other code works. It catches regressions, documents behavior, and gives you the confidence to refactor. This module covers PHPUnit, the de facto testing framework for PHP — no Laravel, no Symfony, just plain PHP.

---

## 1. Installing PHPUnit

### Via Composer (per project — recommended)

```bash
cd my-project/
composer require --dev phpunit/phpunit
```

The binary will be at `./vendor/bin/phpunit`.

### Globally (any project)

```bash
wget -O phpunit.phar https://phar.phpunit.de/phpunit-13.phar
chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit
```

### Verify

```bash
phpunit --version
# PHPUnit 11.x.y by Sebastian Bergmann and contributors.
```

> **Note:** This module uses PHPUnit 13.x (current stable as of 2026). Older codebases may use 10.x or 9.x — the core API is similar. PHPUnit 13 enforces that the test class name matches the filename exactly (PSR-4).

---

## 2. Your First Test

```php
<?php

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testAddsTwoNumbers(): void
    {
        $this->assertEquals(5, add(2, 3));
    }
}
```

The function under test, in `src/Calculator.php`:

```php
<?php

function add(int $a, int $b): int
{
    return $a + $b;
}
```

PHPUnit conventions:
- Test classes end with `Test` and extend `TestCase`.
- Test methods are `public` and prefixed with `test`.
- The test file mirrors the source: `src/Calculator.php` → `tests/CalculatorTest.php`.

Run it:

```bash
./vendor/bin/phpunit tests/CalculatorTest.php
```

---

## 3. Project Structure

```
my-project/
├── src/
│   └── Calculator.php
├── tests/
│   └── CalculatorTest.php
├── vendor/             # installed by Composer
├── composer.json
└── phpunit.xml
```

Basic `composer.json`:

```json
{
    "autoload": {
        "files": ["src/Calculator.php"]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

Run `composer dump-autoload` after changes.

---

## 4. Most Useful Assertions

```php
<?php

use PHPUnit\Framework\TestCase;

class AssertionsTest extends TestCase
{
    public function testAssertionsCatalog(): void
    {
        // Equality
        $this->assertEquals(5, 2 + 3);              // == loose
        $this->assertSame(5, 2 + 3);                // === strict

        // Truth / false
        $this->assertTrue($condition);
        $this->assertFalse($condition);
        $this->assertNull($value);
        $this->assertNotNull($value);

        // Strings
        $this->assertStringContainsString('World', 'Hello World');
        $this->assertMatchesRegularExpression('/^\d{3}\.?\d{3}/', '123.456-78');

        // Arrays & Countables
        $this->assertCount(3, ['a', 'b', 'c']);
        $this->assertContains('PHP', ['PHP', 'JS', 'Go']);
        $this->assertArrayHasKey('name', ['name' => 'Dre']);

        // Instance & Type
        $this->assertInstanceOf(\DateTime::class, new \DateTime());
        $this->assertIsArray($someArray);
        $this->assertIsInt($someNumber);

        // Filesystem
        $this->assertFileExists('/path/to/file.txt');
        $this->assertDirectoryExists('/some/dir');
    }
}
```

The full list: [PHPUnit Assertions](https://docs.phpunit.de/en/13.2/assertions.html)

---

## 5. setUp() and tearDown() — Shared Fixtures

Run before and after **each** test method:

```php
<?php

use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private ?PDO $pdo = null;
    private ?UserRepository $repository = null;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
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
}
```

For setup that runs **once per class** (slower but cheaper), use `setUpBeforeClass()` / `tearDownAfterClass()` — must be `static`.

---

## 6. Data Providers

When the same test logic applies to many inputs:

```php
<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MathTest extends TestCase
{
    #[DataProvider('additionProvider')]
    public function testAdd(int $a, int $b, int $expected): void
    {
        $this->assertEquals($expected, add($a, $b));
    }

    public static function additionProvider(): array
    {
        return [
            'positives'       => [2, 3, 5],
            'zeros'           => [0, 0, 0],
            'negative result' => [-2, 1, -1],
            'negative inputs' => [-5, -3, -8],
        ];
    }
}
```

The key in the array (`positives`, `zeros`, etc.) becomes the test name suffix, making failures easy to identify. In PHPUnit 11, you can use the `#[DataProvider]` attribute or the older `@dataProvider` docblock annotation — either works.

---

## 7. Testing Exceptions

```php
<?php

use PHPUnit\Framework\TestCase;

class DivisionTest extends TestCase
{
    public function testThrowsOnDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        $this->expectExceptionMessageIs('Division by zero');

        divide(10, 0);
    }

    public function testThrowsCustomException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);

        withdraw(-50); // negative amount should throw
    }
}
```

The `expectException` must be called **before** the line that triggers it.

> **PHPUnit 13.2+:** `expectExceptionMessage()` is soft-deprecated. Use `expectExceptionMessageIs()` for exact match or `expectExceptionMessageIsOrContains()` for partial match. `expectExceptionCode()` is unchanged.

---

## 8. Mocks and Stubs

PHPUnit has a built-in mock engine — you don't need Mockery or Prophecy for most cases.

### Stub: Fixed Return Values, No Real Logic

```php
<?php

use PHPUnit\Framework\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function testSendsInvoice(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')
               ->willReturn(true);

        $service = new InvoiceService($mailer);
        $result = $service->sendInvoice('dre@email.com', 199.90);

        $this->assertTrue($result);
    }
}
```

`createStub()` returns an object where every method returns `null` by default. You override specific methods with `method()` and `willReturn()`.

### Mock: Verify That Methods Were Called

A mock replaces the real object and verifies interactions. Here the real `Mailer` has side effects (`echo`), but the mock prevents them from running:

```php
<?php

class Mailer
{
    public function send(string $to, string $subject, string $body): bool
    {
        echo "[SENT] To: {$to} | {$subject}: {$body}\n";
        return true;
    }
}

class InvoiceService
{
    public function __construct(private Mailer $mailer) {}

    public function sendInvoice(string $to, float $amount): bool
    {
        $body = "Your invoice: amount: " . number_format($amount, 2);
        return $this->mailer->send($to, 'Your invoice', $body);
    }
}
```

```php
<?php

use PHPUnit\Framework\TestCase;

class InvoiceMockTest extends TestCase
{
    public function testMockPreventsRealMailerFromRunning(): void
    {
        $mailer = $this->createMock(Mailer::class);

        $mailer->expects($this->once())
               ->method('send')
               ->with('dre@email.com', 'Your invoice', $this->stringContains('amount: 199.90'))
               ->willReturn(true);

        $service = new InvoiceService($mailer);
        $service->sendInvoice('dre@email.com', 199.90);
        // Nothing echoes — Mailer::send() was never really called.
        // If send() isn't called with these exact args, PHPUnit fails the test.
    }
}
```

Available matchers for `expects()`:
- `$this->once()` — exactly one call
- `$this->exactly(3)` — exactly N calls
- `$this->never()` — zero calls
- `$this->atLeast(1)` — N or more calls

### Stub vs Mock: When to Use What

Use a **stub** when you just need a dummy to return values and you'll assert on your own code's result.

Use a **mock** when the interaction with the dependency matters (e.g., "did we call the mailer exactly once?", "did we pass the right email?").

> In both cases, the real dependency is **never** executed — that's the whole point.

---

## 9. Mockery — Spies and a More Expressive API

Mockery (`mockery/mockery`) is to PHPUnit what Mockito is to JUnit: a standalone mocking library that adds spies, partial mocks, and a fluent assertion style. Install it:

```bash
composer require --dev mockery/mockery
```

### Spy: Record First, Assert Later

Unlike PHPUnit mocks (where you declare expectations **before** calling the code under test), a spy lets you run the code first and **then** ask what happened. Much closer to Jest's `expect(fn).toHaveBeenCalled()`.

```php
<?php

class Mailer
{
    public function send(string $to, string $subject, string $body): bool
    {
        echo "[REAL SEND] {$to}: {$subject}\n";
        return true;
    }
}

class InvoiceService
{
    public function __construct(private Mailer $mailer) {}

    public function sendInvoice(string $to, float $amount): bool
    {
        $body = "amount: " . number_format($amount, 2);
        return $this->mailer->send($to, 'Your invoice', $body);
    }
}
```

```php
<?php

use PHPUnit\Framework\TestCase;
use Mockery;

class InvoiceSpyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSpyRecordsThatMailerWasCalled(): void
    {
        $spy = Mockery::spy(Mailer::class);
        $spy->allows()->send(Mockery::any(), Mockery::any(), Mockery::any())
                      ->andReturn(true);

        $service = new InvoiceService($spy);
        $result = $service->sendInvoice('dre@email.com', 199.90);

        // Real Mailer never ran — no echo happened
        $this->assertTrue($result);

        // Assert after the fact, Jest style
        $spy->shouldHaveReceived('send')
            ->once()
            ->with('dre@email.com', 'Your invoice', Mockery::pattern('/amount: 199\.\d{2}/'));
    }
}
```

### PHPUnit Mock vs Mockery Spy

| | PHPUnit `createMock` | Mockery `spy` |
|---|---|---|
| When expectations are set | Before calling SUT | After calling SUT |
| Fails if method not called | Automatically | Only if you assert with `shouldHaveReceived` |
| Style | Declare-then-act | Act-then-assert (Jest-like) |
| Real method runs? | No | No |

### Partial Mock: Spy on a Real Object

Mockery can also wrap a real object, letting specific methods run normally while you spy on them:

```php
<?php

$mailer = Mockery::mock(new Mailer())->makePartial();
// The real Mailer's constructor and internal state are preserved
// Only methods you stub are replaced

$service = new InvoiceService($mailer);
$result = $service->sendInvoice('dre@email.com', 199.90);

$mailer->shouldHaveReceived('send')->once();
```

> **Note:** Mockery is a separate library, not built into PHPUnit. Always call `Mockery::close()` in `tearDown()` to detect unmet expectations.

---

## 10. Testing Output

PHPUnit uses PHP's output buffering (`ob_start()` + `ob_get_clean()`) behind the scenes to capture everything your code echoes, then compares it against your expectations. You don't need to call `ob_start()` yourself — just use `expectOutputString()` or `expectOutputRegex()`.

```php
<?php

function renderTemplate(string $name): void
{
    echo "<h1>Hello {$name}</h1>\n";
}
```

```php
<?php

use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    public function testRendersHTML(): void
    {
        $this->expectOutputString("<h1>Hello World</h1>\n");

        renderTemplate('World');
    }

    public function testCapturesPartialOutput(): void
    {
        echo "Start\n";
        echo "Middle\n";
        echo "End\n";

        $this->expectOutputRegex('/Middle/');
    }
}
```

`expectOutputString()` requires an exact match (including whitespace and newlines). `expectOutputRegex()` matches against the full output buffer.

---

## 11. Testing Functions That Call `exit()` or `die()`

Plain PHP functions that call `exit()` or `die()` will kill the test runner unless you handle them.

Option 1: wrap in a try/catch with a custom exception:

```php
<?php

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

// Test helper:
class ExitException extends \RuntimeException {}

function redirectForTest(string $url): never
{
    throw new ExitException("redirected to {$url}");
}

class RedirectTest extends TestCase
{
    public function testRedirectUrl(): void
    {
        $this->expectException(ExitException::class);
        $this->expectExceptionMessageIs('redirected to /login');

        redirectForTest('/login');
    }
}
```

Option 2: restructure your code so `exit()` only lives at the very edge (controllers), and core logic returns values that can be tested normally. This is better architecture anyway.

---

## 12. Testing HTTP Responses (Without Framework)

Use output buffering to capture what your script rendered:

```php
<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testReturnsJSON(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        require 'api/users.php';
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('users', $response);
    }
}
```

For superglobals (`$_GET`, `$_POST`, `$_SESSION`), set them in your test before including the file. Always clean up in `tearDown()`:

```php
<?php

class FormTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER = [];
    }

    public function testFormSubmission(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'dre@email.com';

        ob_start();
        require 'process.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Email saved', $output);
    }
}
```

---

## 13. Testing Database Code

The cleanest approach: use an in-memory SQLite database that gets created and destroyed every test.

```php
<?php

use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private ?PDO $pdo = null;
    private ?UserRepository $repo = null;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE
            )
        ');

        $this->repo = new UserRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->repo = null;
    }

    public function testCreatesUser(): void
    {
        $id = $this->repo->create('Dre', 'dre@email.com');

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testFindsUserById(): void
    {
        $id = $this->repo->create('Dre', 'dre@email.com');
        $user = $this->repo->findById($id);

        $this->assertEquals('Dre', $user['name']);
        $this->assertEquals('dre@email.com', $user['email']);
    }

    public function testFailsOnDuplicateEmail(): void
    {
        $this->repo->create('Dre', 'dre@email.com');

        $this->expectException(\Exception::class);
        $this->repo->create('Other Dre', 'dre@email.com');
    }
}
```

If you use MySQL-specific syntax (e.g., JSON columns, fulltext), create a separate test database. Document the schema in your test setup or use migration files.

---

## 14. Organizing Tests with `phpunit.xml`

Create `phpunit.xml` in the project root:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="tests/bootstrap.php"
    colors="true"
    cacheDirectory=".phpunit.cache"
>
    <testsuites>
        <testsuite name="unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DSN" value="sqlite::memory:"/>
    </php>
</phpunit>
```

`tests/bootstrap.php`:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
```

Now you can run filtered suites:

```bash
phpunit                           # all tests
phpunit --testsuite=unit          # only unit tests
phpunit --filter=testCreatesUser  # single test method
phpunit --filter=UserRepositoryTest  # single class
```

---

## 15. TDD Workflow: Red → Green → Refactor

Real example — building a CPF validator:

### Step 1 — Write a failing test (RED)

```php
<?php

use PHPUnit\Framework\TestCase;

class CpfValidatorTest extends TestCase
{
    public function testValidCpf(): void
    {
        $this->assertTrue(validateCpf('529.982.247-25'));
    }

    public function testInvalidCpf(): void
    {
        $this->assertFalse(validateCpf('111.111.111-11'));
    }

    public function testRejectsWrongLength(): void
    {
        $this->assertFalse(validateCpf('123'));
    }

    public function testRejectsLetters(): void
    {
        $this->assertFalse(validateCpf('abc.def.ghi-jk'));
    }
}
```

Run: `phpunit --filter=CpfValidatorTest` → 4 failures. Good.

### Step 2 — Make it pass (GREEN)

```php
<?php

function validateCpf(string $cpf): bool
{
    $cpf = preg_replace('/\D/', '', $cpf);

    if (strlen($cpf) !== 11) {
        return false;
    }

    if (!is_numeric($cpf)) {
        return false;
    }

    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $sum = 0;
        for ($i = 0; $i < $t; $i++) {
            $sum += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digit = ($sum * 10) % 11 % 10;
        if ((int) $cpf[$t] !== $digit) {
            return false;
        }
    }

    return true;
}
```

Run again → 4 passes.

### Step 3 — Refactor

Maybe extract digit calculation, rename variables for clarity, add type hints. Run tests again after every change to make sure nothing broke.

---

## 16. Testing Static Methods and Plain Functions

PHPUnit tests these just like any other function:

```php
<?php

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

class SlugifyTest extends TestCase
{
    #[DataProvider('slugProvider')]
    public function testSlugify(string $input, string $expected): void
    {
        $this->assertEquals($expected, slugify($input));
    }

    public static function slugProvider(): array
    {
        return [
            'simple'                    => ['Hello World', 'hello-world'],
            'accents (unhandled)'       => ['caçamba', 'ca-amba'],
            'leading trailing hyphens'  => ['--hello--', 'hello'],
            'multiple spaces'           => ['php   is   great', 'php-is-great'],
            'already a slug'            => ['my-post', 'my-post'],
            'numbers'                   => ['PHP 8.5 rocks', 'php-8-5-rocks'],
        ];
    }
}
```

Same works for `private static` methods if you use reflection — but prefer testing through the public API. If a private method is complex enough to need its own tests, consider extracting it to its own class.

---

## 17. Skipping and Incomplete Tests

```php
<?php

class TodoTest extends TestCase
{
    public function testFeatureNotImplementedYet(): void
    {
        $this->markTestIncomplete('Waiting for API v2');
    }

    public function testOnlyOnPHP85(): void
    {
        if (PHP_VERSION_ID < 80500) {
            $this->markTestSkipped('Requires PHP 8.5+');
        }

        $this->assertTrue(/* PHP 8.5 feature */ true);
    }
}
```

---

## Labs

Run from `labs/class16/`:

| Lab | Folder | Topic |
|-----|--------|-------|
| 01 | `01-calculator/` | First test (Calculator) |
| 02 | `02-assertions/` | Assertions catalog |
| 03 | `03-user-repository/` | setUp/tearDown + SQLite |
| 04 | `04-data-providers/` | Data providers |
| 05 | `05-exceptions/` | Testing exceptions |
| 06 | `06-stubs/` | Stubs |
| 07 | `07-mocks/` | Mocks |
| 08 | `08-mockery/` | Mockery: spies and partial mocks |
| 09 | `09-output/` | Output testing |
| 10 | `10-exit-handling/` | Handling exit()/die() |
| 11 | `11-http/` | HTTP superglobals |
| 12 | `12-config/` | phpunit.xml + bootstrap |
| 13 | `13-tdd-cpf/` | TDD: CPF validator |
| 14 | `14-slugify/` | Data providers: slugify |
| 15 | `15-skipping/` | Skipping/incomplete tests |

```bash
./vendor/bin/phpunit 01-calculator    # single lab
./vendor/bin/phpunit */               # all labs
```

---

## Navigation

- [← Module 15: PHP Security](./15-seguranca.md)

## References

- [PHPUnit Official Docs](https://docs.phpunit.de/en/13.2/)
- [PHPUnit: Writing Tests](https://docs.phpunit.de/en/13.2/writing-tests-for-phpunit.html)
- [PHPUnit: Assertions](https://docs.phpunit.de/en/13.2/assertions.html)
- [PHPUnit: Test Doubles](https://docs.phpunit.de/en/13.2/test-doubles.html)
- [PHPUnit: Organizing Tests](https://docs.phpunit.de/en/13.2/organizing-tests.html)
- [PHPUnit: Configuration](https://docs.phpunit.de/en/13.2/configuration.html)
- [PHP: The Right Way — Testing](https://phptherightway.com/#testing)
- [Packagist](https://packagist.org) — PHP package repository (like npm/Maven Central)
- [Test-Driven Development by Example — Kent Beck](https://www.amazon.com/Test-Driven-Development-Kent-Beck/dp/0321146530)
- [PHP Package Checklist — Testing](https://phppackagechecklist.com/#testing)
