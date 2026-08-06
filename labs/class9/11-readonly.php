<?php

// === 11 — Readonly Properties (PHP 8.1+) and Readonly Classes (PHP 8.2+) ===

class Client
{
    public function __construct(
        public readonly string $cpf,
        public readonly string $name,
        public readonly DateTimeImmutable $registrationDate = new DateTimeImmutable(),
    ) {}

    public function getYear(): int
    {
        return (int) $this->registrationDate->format('Y');
    }
}

$client = new Client('123.456.789-00', 'Mary Silva');
echo $client->name;      // Mary Silva
echo $client->getYear();  // 2026

// $client->name = 'Other'; // Error: readonly property

// === Readonly Classes (PHP 8.2+) ===

readonly class Address
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $zipCode,
    ) {}
}

$end = new Address('Paulista Ave', 'Sao Paulo', 'SP', '01310-100');
echo "{$end->street}, {$end->city} - {$end->state}, {$end->zipCode}";
// Paulista Ave, Sao Paulo - SP, 01310-100

// $end->city = 'Rio'; // Error: readonly class
