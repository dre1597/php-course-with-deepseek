<?php

// === 06 — Interfaces (contracts, multiple implementation, interface constants, inheritance) ===

interface Loggable
{
    public function getLogMessage(): string;
    public function getLogLevel(): string;
}

interface JsonSerializableCustom
{
    public function toJson(): string;
}

class Event implements Loggable, JsonSerializableCustom
{
    public function __construct(
        private string $name,
        private array $payload,
    ) {}

    public function getLogMessage(): string
    {
        return "Event: {$this->name} — " . json_encode($this->payload);
    }

    public function getLogLevel(): string
    {
        return 'info';
    }

    public function toJson(): string
    {
        return json_encode([
            'event'  => $this->name,
            'data'   => $this->payload,
        ], JSON_UNESCAPED_UNICODE);
    }
}

function register(Loggable $item): void
{
    echo "[{$item->getLogLevel()}] {$item->getLogMessage()}" . PHP_EOL;
}

$event = new Event('user.login', ['user_id' => 42, 'ip' => '192.168.1.1']);
register($event); // [info] Event: user.login — {"user_id":42,"ip":"192.168.1.1"}
echo $event->toJson();

// === Interface with Constants (PHP 8.1+) ===

interface Rates
{
    public const float ICMS   = 0.18;
    public const float ISS    = 0.05;
    public const float PIS    = 0.0165;
    public const float COFINS = 0.076;
}

class Invoice implements Rates
{
    public function calculateTaxes(float $value): float
    {
        return $value * (Rates::ICMS + Rates::ISS + Rates::PIS + Rates::COFINS);
    }
}

$invoice = new Invoice();
echo "Taxes: $ " . $invoice->calculateTaxes(1000);
// Taxes: $ 322.5

// === Interface Inheritance ===

interface Contractable
{
    public function sign(): void;
}

interface Renewable extends Contractable
{
    public function renew(): void;
}

class ServiceContract implements Renewable
{
    public function sign(): void
    {
        echo "Contract signed.\n";
    }

    public function renew(): void
    {
        echo "Contract renewed.\n";
    }
}
