<?php

// === 05 — Custom Exceptions ===

// Domain base exception
class DomainException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getTitle(): string
    {
        return 'Domain Error';
    }
}

// Specific exceptions
class InsufficientBalanceException extends DomainException
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $requestedAmount,
    ) {
        $difference = number_format($this->requestedAmount - $this->currentBalance, 2, '.', ',');
        parent::__construct(
            "Insufficient balance. Balance: $ {$this->currentBalance}, " .
            "Requested: $ {$this->requestedAmount}. " .
            "Shortfall: $ {$difference}"
        );
    }

    public function getTitle(): string
    {
        return 'Insufficient Balance';
    }
}

class AccountBlockedException extends DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct("Account blocked: {$reason}");
    }
}

// Using custom exceptions
class Account
{
    private bool $blocked = false;

    public function __construct(
        private string $holder,
        private float $balance = 0.0,
    ) {}

    public function withdraw(float $value): void
    {
        if ($this->blocked) {
            throw new AccountBlockedException('Account under investigation');
        }

        if ($value > $this->balance) {
            throw new InsufficientBalanceException(
                currentBalance: $this->balance,
                requestedAmount: $value,
            );
        }

        $this->balance -= $value;
    }

    public function block(): void
    {
        $this->blocked = true;
    }
}

// Controller / Service
$account = new Account('Mary', 100.00);

try {
    $account->withdraw(500.00);
} catch (InsufficientBalanceException $e) {
    echo $e->getTitle() . ': ' . $e->getMessage() . PHP_EOL;
    // Insufficient Balance: Insufficient balance. Balance: $ 100, ...
} catch (AccountBlockedException $e) {
    echo $e->getMessage() . PHP_EOL;
} catch (DomainException $e) {
    echo $e->getMessage() . PHP_EOL;
}

// === Exception Chaining ===

class RepositoryException extends Exception {}

function saveToDatabase(array $dataPayload): void
{
    try {
        // Simulating PDO connection failure
        throw new PDOException('Connection refused');
    } catch (PDOException $e) {
        throw new RepositoryException(
            "Failed to save user: {$dataPayload['name']}",
            0,
            $e, // chains the original exception
        );
    }
}

try {
    saveToDatabase(['name' => 'John']);
} catch (RepositoryException $e) {
    echo $e->getMessage() . PHP_EOL;
    echo "Root cause: " . $e->getPrevious()->getMessage() . PHP_EOL;
}
// Failed to save user: John
// Root cause: Connection refused
