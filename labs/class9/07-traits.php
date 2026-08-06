<?php

// === 07 — Traits (horizontal reuse, conflicts, abstract methods in traits) ===

trait Timestamps
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    private function initTimestamps(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

trait HasUuid
{
    private string $uuid;

    private function initUuid(): void
    {
        $this->uuid = bin2hex(random_bytes(16));
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}

class Post
{
    use Timestamps, HasUuid;

    public function __construct(
        private string $title,
        private string $content,
    ) {
        $this->initTimestamps();
        $this->initUuid();
    }

    public function edit(string $content): void
    {
        $this->content = $content;
        $this->touch();
    }
}

$post = new Post('PHP 8.5', 'New features...');
echo $post->getUuid() . PHP_EOL;            // ex: a1b2c3d4...
echo $post->getCreatedAt()->format('c');      // 2026-08-04T10:30:00+00:00

// === Conflict Resolution in Traits ===

trait LoggerJson
{
    public function formatLog(string $msg): string
    {
        return json_encode(['message' => $msg]);
    }
}

trait TextLogger
{
    public function formatLog(string $msg): string
    {
        return "[LOG] {$msg}";
    }
}

class MyLogger
{
    use LoggerJson, TextLogger {
        LoggerJson::formatLog insteadof TextLogger;  // use LoggerJson's version
        TextLogger::formatLog as formatLogText;      // alias
    }
}

$logger = new MyLogger();
echo $logger->formatLog('test');      // {"message":"test"}
echo $logger->formatLogText('test');  // [LOG] test

// === Traits with Abstract Methods ===

trait Nameable
{
    abstract public function getName(): string;

    public function getDisplayName(): string
    {
        return mb_strtoupper($this->getName());
    }
}

class Category
{
    use Nameable;

    public function __construct(private string $name) {}

    public function getName(): string
    {
        return $this->name;
    }
}

echo (new Category('electronics'))->getDisplayName(); // ELECTRONICS
