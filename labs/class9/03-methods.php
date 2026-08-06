<?php

// === 03 — Methods (visibility on methods) ===

class Calculator
{
    public function sum(int $first, int $second): int
    {
        return $first + $second;
    }

    protected function validateOperation(string $operation): bool
    {
        return in_array($operation, ['+', '-', '*', '/']);
    }

    private function log(string $message): void
    {
        error_log("[Calculator] {$message}");
    }

    public function execute(string $operation, int $first, int $second): int|float
    {
        if (!$this->validateOperation($operation)) {
            throw new InvalidArgumentException("Invalid operation: {$operation}");
        }

        $this->log("Executing {$first} {$operation} {$second}");

        return match ($operation) {
            '+' => $first + $second,
            '-' => $first - $second,
            '*' => $first * $second,
            '/' => $first / $second,
        };
    }
}

$calc = new Calculator();
echo $calc->execute('*', 6, 7); // 42
// $calc->validateOperation('*');   // Error: protected method
// $calc->log('test');             // Error: private method
