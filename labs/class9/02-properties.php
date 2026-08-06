<?php

// === 02 — Properties (visibility: public, protected, private) ===

class BankAccount
{
    private string $holder;
    private float $balance = 0.0;
    protected string $type = 'checking';

    public function __construct(string $holder)
    {
        $this->holder = $holder;
    }

    public function deposit(float $value): void
    {
        if ($value > 0) {
            $this->balance += $value;
        }
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getHolder(): string
    {
        return $this->holder;
    }
}

$account = new BankAccount('John Doe');
$account->deposit(1000);
echo $account->getBalance();   // 1000

// $account->balance = 9999;   // Error: private property
// $account->type = 'savings'; // Error: protected property

// === Typed Properties (PHP 7.4+) ===

class User
{
    public string $name;
    public int $age;
    public ?string $phone = null;  // nullable, with default value
    public bool $active = true;
}

$user = new User();
$user->name  = 'Mary';
$user->age = 28;

// $u->name = 123; // TypeError — expected string

// Accessing without initializing (non-nullable property):
// $u2 = new User();
// echo $u2->name; // Error: uninitialized property
