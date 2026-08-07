<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EventDispatcher.php';

class EventDispatcherTest extends TestCase
{
    public function testRegisterAndDispatchSingleListener(): void
    {
        $dispatcher = new EventDispatcher();
        $called = false;

        $dispatcher->on('user.created', function () use (&$called) {
            $called = true;
        });

        $dispatcher->dispatch('user.created');

        $this->assertTrue($called);
    }

    public function testDispatchPassesArgumentsToListener(): void
    {
        $dispatcher = new EventDispatcher();
        $received = null;

        $dispatcher->on('user.created', function ($arg) use (&$received) {
            $received = $arg;
        });

        $dispatcher->dispatch('user.created', 'John');

        $this->assertSame('John', $received);
    }

    public function testDispatchPassesMultipleArgumentsToListener(): void
    {
        $dispatcher = new EventDispatcher();
        $received = [];

        $dispatcher->on('order.placed', function ($arg1, $arg2, $arg3) use (&$received) {
            $received = [$arg1, $arg2, $arg3];
        });

        $dispatcher->dispatch('order.placed', 42, 'shipped', 199.90);

        $this->assertSame([42, 'shipped', 199.90], $received);
    }

    public function testMultipleListenersForSameEventAreCalled(): void
    {
        $dispatcher = new EventDispatcher();
        $count = 0;

        $dispatcher->on('user.created', function () use (&$count) {
            $count++;
        });
        $dispatcher->on('user.created', function () use (&$count) {
            $count++;
        });
        $dispatcher->on('user.created', function () use (&$count) {
            $count++;
        });

        $dispatcher->dispatch('user.created');

        $this->assertSame(3, $count);
    }

    public function testMultipleListenersAreCalledInRegistrationOrder(): void
    {
        $dispatcher = new EventDispatcher();
        $order = [];

        $dispatcher->on('user.created', function () use (&$order) {
            $order[] = 'first';
        });
        $dispatcher->on('user.created', function () use (&$order) {
            $order[] = 'second';
        });
        $dispatcher->on('user.created', function () use (&$order) {
            $order[] = 'third';
        });

        $dispatcher->dispatch('user.created');

        $this->assertSame(['first', 'second', 'third'], $order);
    }

    public function testDispatchOnlyTriggersListenersForThatEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $calledUserCreated = false;
        $calledOrderPlaced = false;

        $dispatcher->on('user.created', function () use (&$calledUserCreated) {
            $calledUserCreated = true;
        });
        $dispatcher->on('order.placed', function () use (&$calledOrderPlaced) {
            $calledOrderPlaced = true;
        });

        $dispatcher->dispatch('user.created');

        $this->assertTrue($calledUserCreated);
        $this->assertFalse($calledOrderPlaced);
    }

    public function testDispatchWithNoRegisteredListenersThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs("No listeners registered for event 'nonexistent.event'.");

        $dispatcher->dispatch('nonexistent.event');
    }

    public function testListenerCanBeAnArrayCallable(): void
    {
        $dispatcher = new EventDispatcher();

        $handler = new class {
            public string $name = '';

            public function handle(string $name): void
            {
                $this->name = $name;
            }
        };

        $dispatcher->on('user.created', [$handler, 'handle']);
        $dispatcher->dispatch('user.created', 'Alice');

        $this->assertSame('Alice', $handler->name);
    }

    public function testListenerCanBeAnInvokableObject(): void
    {
        $dispatcher = new EventDispatcher();

        $handler = new class {
            public ?string $data = null;

            public function __invoke(string $data): void
            {
                $this->data = $data;
            }
        };

        $dispatcher->on('data.received', $handler);
        $dispatcher->dispatch('data.received', 'payload');

        $this->assertSame('payload', $handler->data);
    }

    public function testMultipleEventsTriggerCorrectListenersIndependently(): void
    {
        $dispatcher = new EventDispatcher();
        $userLog = [];
        $orderLog = [];

        $dispatcher->on('user.created', function ($name) use (&$userLog) {
            $userLog[] = "user:{$name}";
        });
        $dispatcher->on('user.deleted', function ($name) use (&$userLog) {
            $userLog[] = "deleted:{$name}";
        });
        $dispatcher->on('order.placed', function ($id) use (&$orderLog) {
            $orderLog[] = "order:{$id}";
        });

        $dispatcher->dispatch('user.created', 'Alice');
        $dispatcher->dispatch('order.placed', 101);
        $dispatcher->dispatch('user.deleted', 'Bob');

        $this->assertSame(['user:Alice', 'deleted:Bob'], $userLog);
        $this->assertSame(['order:101'], $orderLog);
    }

    public function testOnWithEmptyEventNameThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Event name cannot be empty.');

        $dispatcher->on('', function () {});
    }

    public function testOnWithWhitespaceOnlyEventNameThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Event name cannot be empty.');

        $dispatcher->on('   ', function () {});
    }

    public function testOnWithNonCallableThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Listener must be callable.');

        $dispatcher->on('user.created', 'not_a_function');
    }

    public function testOnWithArrayMissingMethodThrowsException(): void
    {
        $dispatcher = new EventDispatcher();
        $handler = new class {};

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Listener must be callable.');

        $dispatcher->on('user.created', [$handler, 'nonexistentMethod']);
    }

    public function testOnWithNonCallableObjectThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Listener must be callable.');

        $dispatcher->on('user.created', new \stdClass());
    }

    public function testDispatchWithEmptyEventNameThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Event name cannot be empty.');

        $dispatcher->dispatch('');
    }

    public function testDispatchWithWhitespaceOnlyEventNameThrowsException(): void
    {
        $dispatcher = new EventDispatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Event name cannot be empty.');

        $dispatcher->dispatch('   ');
    }

    public function testExceptionThrownByListenerPropagates(): void
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->on('dangerous.event', function () {
            throw new \RuntimeException('Something went wrong in the listener.');
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs('Something went wrong in the listener.');

        $dispatcher->dispatch('dangerous.event');
    }

    public function testSameListenerRegisteredMultipleTimesRunsMultipleTimes(): void
    {
        $dispatcher = new EventDispatcher();
        $count = 0;

        $listener = function () use (&$count) {
            $count++;
        };

        $dispatcher->on('user.created', $listener);
        $dispatcher->on('user.created', $listener);
        $dispatcher->on('user.created', $listener);

        $dispatcher->dispatch('user.created');

        $this->assertSame(3, $count);
    }
}
