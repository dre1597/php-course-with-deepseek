<?php

// === 14 — Asymmetric Visibility (PHP 8.4+) ===

class Report
{
    // Everyone reads, only the class modifies
    public private(set) string $title;

    // Everyone reads, class and subclasses modify
    public protected(set) int $views = 0;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function incrementView(): void
    {
        $this->views++;
    }
}

class PremiumReport extends Report
{
    public function resetViews(): void
    {
        $this->views = 0; // OK — protected(set)
        // $this->title = 'new';        // Error — private(set) not accessible in subclass
    }
}

$report = new Report('Q3 Sales');
echo $report->title;              // Q3 Sales — public get

// $report->title = 'Q4';          // Error — private(set)
// $report->views = 100;    // Error — protected(set)

// ---

// Example 1: UUID generated once, externally visible
class Entity
{
    public private(set) string $id;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(16));
    }
}

// Example 2: Counter only incrementable internally
class Visitor
{
    public protected(set) int $accessCount = 0;

    public function registerAccess(): void
    {
        $this->accessCount++;
    }
}

// Example 3: Configuration only changeable via method
class Connection
{
    public private(set) string $host;
    public private(set) int $port;

    public function __construct(string $host, int $port)
    {
        $this->host  = $host;
        $this->port = $port;
    }

    public function reconnect(string $host, int $port): void
    {
        $this->close();
        $this->host  = $host;
        $this->port = $port;
    }

    private function close(): void { /* ... */ }
}
