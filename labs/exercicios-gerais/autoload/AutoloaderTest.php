<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Autoloader.php';

class AutoloaderTest extends TestCase
{
    protected function setUp(): void
    {
        Autoloader::addNamespace('App', __DIR__ . '/src');
        Autoloader::register();
    }

    public function testAutoloaderResolvesModelClass(): void
    {
        $user = new \App\Models\User(1, 'Alice', 'alice@email.com');

        $this->assertInstanceOf(\App\Models\User::class, $user);
        $this->assertSame(1, $user->id);
        $this->assertSame('Alice', $user->name);
        $this->assertSame('alice@email.com', $user->email);
    }

    public function testAutoloaderResolvesControllerClass(): void
    {
        $controller = new \App\Controllers\HomeController();

        $this->assertInstanceOf(\App\Controllers\HomeController::class, $controller);
    }

    public function testModelToArrayReturnsCorrectData(): void
    {
        $user = new \App\Models\User(42, 'Bob', 'bob@email.com');

        $this->assertSame([
            'id'    => 42,
            'name'  => 'Bob',
            'email' => 'bob@email.com',
        ], $user->toArray());
    }

    public function testControllerIndexReturnsWelcomeMessage(): void
    {
        $controller = new \App\Controllers\HomeController();

        $this->assertSame('Welcome to the Home Page.', $controller->index());
    }

    public function testControllerGreetsUserByName(): void
    {
        $user = new \App\Models\User(1, 'Charlie', 'charlie@email.com');
        $controller = new \App\Controllers\HomeController();

        $this->assertSame('Hello, Charlie!', $controller->greet($user));
    }

    public function testMultipleModelsDoNotConflict(): void
    {
        $alice = new \App\Models\User(1, 'Alice', 'alice@email.com');
        $bob   = new \App\Models\User(2, 'Bob', 'bob@email.com');

        $this->assertSame('Alice', $alice->name);
        $this->assertSame('Bob', $bob->name);
        $this->assertSame('alice@email.com', $alice->email);
        $this->assertSame('bob@email.com', $bob->email);
    }

    public function testAutoloaderDoesNotCrashOnUnknownClass(): void
    {
        $this->assertFalse(class_exists('App\Unknown\GhostClass', true));
    }

    public function testAddNamespaceWithTrailingSlashWorks(): void
    {
        Autoloader::addNamespace('App', __DIR__ . '/src/');

        $user = new \App\Models\User(99, 'Trailing', 'slash@email.com');

        $this->assertSame('Trailing', $user->name);
    }

    public function testAddNamespaceWithoutSlashWorks(): void
    {
        Autoloader::addNamespace('App', __DIR__ . '/src');

        $user = new \App\Models\User(88, 'NoSlash', 'noslash@email.com');

        $this->assertSame('NoSlash', $user->name);
    }

    public function testClassInstantiatedTwiceProducesDistinctObjects(): void
    {
        $user1 = new \App\Models\User(1, 'Alice', 'alice@email.com');
        $user2 = new \App\Models\User(1, 'Alice', 'alice@email.com');

        $this->assertEquals($user1->toArray(), $user2->toArray());
        $this->assertNotSame($user1, $user2);
    }
}
