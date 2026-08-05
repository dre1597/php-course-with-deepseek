<?php

// union

function formatId(int|string $id): string
{
    return (string) $id;
}

echo formatId(42);     // "42"
echo formatId("ABC");  // "ABC"

// Retorno com union type
function findUser(string $id): ?array
{
    // ?array is syntactic sugar for array|null
    $users = [
        '1' => ['name' => 'Alice'],
        '2' => ['name' => 'Bob'],
    ];
    return $users[$id] ?? null;
}

// Union com mais de 2 tipos
function process(mixed $value): int|float|string  // mixed = any type (see 'mixed' section below)
{
    return match (true) {
        is_int($value)   => $value * 2,
        is_float($value) => round($value, 2),
        is_string($value) => strtoupper($value),
        default          => throw new \InvalidArgumentException('Invalid type'),
    };
}

// intersection

interface HasName
{
    public function getName(): string;
}

interface HasPrice
{
    public function getPrice(): float;
}

class Product implements HasName, HasPrice
{
    public function __construct(
        private string $name,
        private float  $price,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}

class Service implements HasName, HasPrice
{
    public function __construct(
        private string $name,
        private float  $hourlyRate,
        private int    $hours,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->hourlyRate * $this->hours;
    }
}

// Accepts ANY object that implements BOTH interfaces
function displayPrice(HasName&HasPrice $item): string
{
    return "{$item->getName()}: \$ " . number_format($item->getPrice(), 2, '.', ',');
}

$product = new Product('Keyboard', 250.00);
$service = new Service('Consulting', 150.00, 3);

echo displayPrice($product);
echo displayPrice($service);

function findById(int $id): ?string
{
    // ?string is equivalent to string|null
    if ($id === 0) {
        return null;
    }
    return "Record #{$id}";
}

$result = findById(0);
var_dump($result);

$result = findById(42);
var_dump($result);