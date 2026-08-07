<?php

namespace Models\BankAccount;

use InvalidArgumentException;
use RuntimeException;

class BankAccount
{
    public function __construct(
        private readonly string $owner,
        private readonly string $accountNumber,
        private float           $balance = 0
    )
    {
        if ($owner === '') {
            throw new InvalidArgumentException("Owner name cannot be empty.");
        }

        if ($accountNumber === '') {
            throw new InvalidArgumentException("Account number cannot be empty.");
        }

        if ($balance < 0) {
            throw new InvalidArgumentException("Initial balance cannot be negative.");
        }
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function deposit(float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Deposit amount cannot be negative.");
        }
        $this->balance += $value;
    }

    public function withdraw(float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Withdraw amount cannot be negative.");
        }

        if ($value > $this->balance) {
            throw new RuntimeException("Insufficient balance.");
        }

        $this->balance -= $value;
    }
}
