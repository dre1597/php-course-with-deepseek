<?php

class EventDispatcher
{
    private array $callbacks = [];

    public function on($event, $callback): void
    {
        if (trim($event) === '') {
            throw new InvalidArgumentException('Event name cannot be empty.');
        }

        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Listener must be callable.');
        }

        $this->callbacks[$event][] = $callback;
    }

    public function dispatch($event, ...$args): void
    {
        if (trim($event) === '') {
            throw new InvalidArgumentException('Event name cannot be empty.');
        }

        if (!isset($this->callbacks[$event])) {
            throw new RuntimeException("No listeners registered for event '{$event}'.");
        }

        foreach ($this->callbacks[$event] as $callback) {
            $callback(...$args);
        }
    }
}
