<?php

// === 04 — Multiple Catch Blocks ===

class ApiClient
{
    public function findUser(int $id): array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID must be positive');
        }

        $response = @file_get_contents("https://api.example.com/users/{$id}");

        if ($response === false) {
            throw new RuntimeException('API connection failed');
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Invalid JSON response', json_last_error());
        }

        return $data;
    }
}

$client = new ApiClient();

try {
    $user = $client->findUser(-1);
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage() . PHP_EOL;
    // specific logic for invalid arguments
} catch (RuntimeException $e) {
    echo "Runtime error: " . $e->getMessage() . PHP_EOL;
    // logic for network/IO failures
} catch (JsonException $e) {
    echo "JSON error: " . $e->getMessage() . PHP_EOL;
    // logic for fallback or retry
} catch (Throwable $e) {
    // Catch any other exception or error (generic fallback)
    echo "Unexpected error: " . $e->getMessage() . PHP_EOL;
    error_log($e->getTraceAsString());
}

// === Multiple Exceptions in Same Catch (PHP 7.1+) ===

try {
    // code that may throw multiple exceptions
} catch (InvalidArgumentException | DivisionByZeroError | RangeException $e) {
    // Common handling for these three types
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
