<?php

// === 08 — Abstract Classes ===

abstract class Employee
{
    public function __construct(
        protected string $name,
        protected float $baseSalary,
    ) {}

    abstract public function calculateBonus(): float;

    public function getTotalSalary(): float
    {
        return $this->baseSalary + $this->calculateBonus();
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class Developer extends Employee
{
    public function calculateBonus(): float
    {
        return $this->baseSalary * 0.2; // 20%
    }
}

class Manager extends Employee
{
    public function calculateBonus(): float
    {
        return $this->baseSalary * 0.5; // 50%
    }
}

$dev = new Developer('John', 10000);
$manager = new Manager('Mary', 15000);

echo "{$dev->getName()}: $ {$dev->getTotalSalary()}" . PHP_EOL; // John: $ 12000
echo "{$manager->getName()}: $ {$manager->getTotalSalary()}" . PHP_EOL; // Mary: $ 22500

// $employee = new Employee('Test', 1000); // Error: abstract class
