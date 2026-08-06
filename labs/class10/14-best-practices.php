<?php

// === 14 — Best Practices ===

// 1. Never Show Errors in Production
// CORRECT: Production environment
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// INCORRECT: exposes stack trace to users
// ini_set('display_errors', '1');

// 2. Use Domain-Specific Exceptions
// BAD:
// throw new Exception('Insufficient balance');

// GOOD:
// throw new InsufficientBalanceException(currentBalance: 100.0, requestedAmount: 500.0);

// 3. Don't Suppress Errors with @
// BAD:
$content = @file_get_contents('file_that_may_not_exist.txt');

// GOOD:
if (file_exists('file.txt') && is_readable('file.txt')) {
    $content = file_get_contents('file.txt');
} else {
    // handle file absence
}

// ALTERNATIVE (GOOD): try/catch with throw expression
$content = file_exists('file.txt')
    ? file_get_contents('file.txt')
    : throw new RuntimeException('File not found');

// 4. Always Catch Throwable as Fallback
try {
    // code that may throw anything
} catch (InvalidArgumentException $e) {
    // specific
} catch (RuntimeException $e) {
    // specific
} catch (Throwable $e) {
    // generic fallback — don't let any error escape
    error_log($e);
    echo 'Internal error. Please try again later.';
}

// 5. Use finally for Resource Cleanup
function processFile(string $path): array
{
    $handle = fopen($path, 'r');

    try {
        // processing that may throw exception
        $data = [];
        while (($line = fgetcsv($handle)) !== false) {
            $data[] = $line;
        }
        return $data;
    } finally {
        // Ensures closing even with exception
        if (is_resource($handle)) {
            fclose($handle);
        }
    }
}

// 6. Validate Data with Exceptions (Fail Fast)
class Appointment
{
    public function __construct(
        private DateTimeImmutable $date,
        private string $description,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (mb_strlen($this->description) < 3) {
            throw new InvalidArgumentException('Description must be at least 3 characters');
        }

        if ($this->date < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Date must be in the future');
        }
    }
}

// Validation at creation time — fail fast, no invalid states
try {
    $appointment = new Appointment(new DateTimeImmutable('2020-01-01'), 'A');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage() . PHP_EOL;
}

// 7. Structured Logging
// BAD:
error_log("Error on order 789: payment refused");

// GOOD:
error_log(json_encode([
    'level'      => 'ERROR',
    'message'    => 'Payment refused',
    'order_id'   => 789,
    'timestamp'  => date('c'),
    'gateway'    => 'stripe',
    'code'       => 'card_declined',
], JSON_UNESCAPED_UNICODE));

// 8. Handle Exceptions at the Right Level
// BAD: catch and swallow
try {
    saveOrder($orderData);
} catch (Exception $e) {
    // silent — no one knows it failed
}

// GOOD: catch where you can take action
try {
    saveOrder($orderData);
    notifyClient($orderData);
} catch (RepositoryException $e) {
    // Log and compensation
    $logger->error('Order save failed', ['order' => $orderData]);
    throw $e; // rethrow if can't recover
}
