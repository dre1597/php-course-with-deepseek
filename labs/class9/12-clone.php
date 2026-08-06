<?php

// === 12 — Object Cloning (clone, __clone(), clone...with PHP 8.5+) ===

// clone — Shallow Copy
class Item
{
    public function __construct(public string $name) {}
}

class Cart
{
    public function __construct(
        public array $items = [],
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}
}

$c1 = new Cart(items: [new Item('Notebook')]);
$c2 = clone $c1;

$c2->items[0]->name = 'Mouse';

echo $c1->items[0]->name; // Mouse — modified! (shallow copy)

// __clone() — Control over Cloning
class Document
{
    public function __construct(
        public string $title,
        public DateTimeImmutable $createdAt,
        public ?self $related = null,
    ) {}

    public function __clone(): void
    {
        // Update date on clone
        $this->createdAt = new DateTimeImmutable();

        // Deep clone of related object
        if ($this->related !== null) {
            $this->related = clone $this->related;
        }
    }
}

$original = new Document('Original', new DateTimeImmutable('2026-01-01'));
$copy = clone $original;

echo $original->createdAt->format('Y-m-d'); // 2026-01-01
echo $copy->createdAt->format('Y-m-d');    // 2026-08-04 (current date)

// clone...with — PHP 8.5+
class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock = 0,
    ) {}
}

$product = new Product('Notebook', 3500.00, 10);

// PHP 8.5+: clone with property override
$discountedProduct = clone $product with ['price' => 2999.00];
$productOutOfStock  = clone $product with ['stock' => 0];

echo $product->price;                    // 3500 (original unchanged)
echo $discountedProduct->price;         // 2999
echo $productOutOfStock->stock;        // 0

// Clone with multiple properties:
$newProduct = clone $product with [
    'name'    => 'Notebook Pro',
    'price'   => 4500.00,
    'stock'   => 20,
];

// clone...with on readonly classes
readonly class Configuration
{
    public function __construct(
        public string $host,
        public int $port,
        public bool $debug = false,
    ) {}
}

$dev = new Configuration('localhost', 3306, debug: true);
$prod = clone $dev with ['host' => 'db.production', 'debug' => false];

echo $dev->debug;  // true (unchanged)
echo $prod->debug; // false
