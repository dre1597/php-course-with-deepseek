<?php

// === 04 — Constructor and Constructor Promotion ===

// __construct()
class Book
{
    private string $title;
    private string $author;
    private int $year;

    public function __construct(string $title, string $author, int $year)
    {
        $this->title = $title;
        $this->author  = $author;
        $this->year    = $year;
    }

    public function getDescription(): string
    {
        return "{$this->title}, by {$this->author} ({$this->year})";
    }
}

$book = new Book('Modern PHP', 'Jane Doe', 2026);
echo $book->getDescription(); // Modern PHP, by Jane Doe (2026)

// Before (PHP < 8.0):
class OldBook
{
    private string $title;
    private string $author;
    private int $year;

    public function __construct(string $title, string $author, int $year)
    {
        $this->title = $title;
        $this->author  = $author;
        $this->year    = $year;
    }
}

// After (PHP 8.0+) — Constructor Promotion:
class BookPromoted
{
    public function __construct(
        private string $title,
        private string $author,
        private int $year,
    ) {}

    public function getDescription(): string
    {
        return "{$this->title}, by {$this->author} ({$this->year})";
    }
}

$book2 = new BookPromoted('Modern PHP', 'Jane Doe', 2026);
echo $book2->getDescription(); // Modern PHP, by Jane Doe (2026)

// Constructor Promotion with Default Values
class Configuration
{
    public function __construct(
        private string $host = 'localhost',
        private int $port = 3306,
        private string $user = 'root',
        private bool $debug = false,
    ) {}

    public function getDsn(): string
    {
        return "mysql:host={$this->host};port={$this->port}";
    }
}

$config = new Configuration(host: 'db.production', debug: true);
echo $config->getDsn(); // mysql:host=db.production;port=3306

// Constructor Body with Promotion
class Order
{
    private DateTimeImmutable $createdAt;

    public function __construct(
        private string $id,
        private array $items,
        private float $total,
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Order must have at least one item');
        }
    }
}
