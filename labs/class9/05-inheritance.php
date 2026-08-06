<?php

// === 05 — Inheritance (extends, parent::, #[\Override]) ===

class Vehicle
{
    public function __construct(
        protected string $brand,
        protected string $model,
        protected int $year,
    ) {}

    public function getDescription(): string
    {
        return "{$this->brand} {$this->model} ({$this->year})";
    }

    public function start(): string
    {
        return 'Vehicle started';
    }
}

class Car extends Vehicle
{
    public function __construct(
        string $brand,
        string $model,
        int $year,
        private int $doors = 4,
    ) {
        parent::__construct($brand, $model, $year);
    }

    #[\Override]
    public function start(): string
    {
        return 'Car started — vroom vroom!';
    }

    public function getFullInfo(): string
    {
        return parent::getDescription() . " — {$this->doors} doors";
    }
}

class Motorcycle extends Vehicle
{
    #[\Override]
    public function start(): string
    {
        return 'Motorcycle started — vroom!';
    }
}

$car = new Car('Toyota', 'Corolla', 2026, 4);
echo $car->getFullInfo() . PHP_EOL; // Toyota Corolla (2026) — 4 doors
echo $car->start() . PHP_EOL;       // Car started — vroom vroom!

$motorcycle = new Motorcycle('Honda', 'CB500', 2025);
echo $motorcycle->start();           // Motorcycle started — vroom!

// === parent:: ===

class LoggerBase
{
    protected function format(string $message): string
    {
        return date('[Y-m-d H:i:s] ') . $message;
    }
}

class FileLogger extends LoggerBase
{
    #[\Override]
    protected function format(string $message): string
    {
        return parent::format($message) . ' [FILE]';
    }

    public function log(string $msg): void
    {
        echo $this->format($msg) . PHP_EOL;
    }
}

$logger = new FileLogger();
$logger->log('System started');
// [2026-08-04 10:30:00] System started [FILE]
