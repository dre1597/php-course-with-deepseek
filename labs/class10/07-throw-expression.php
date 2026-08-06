<?php

// === 07 — throw as Expression (PHP 8.0+) ===

// Ternary
$name = $input['name'] ?? throw new InvalidArgumentException('Name is required');

// match
$status = match ($code) {
    200, 201 => 'success',
    400     => 'validation error',
    404     => 'not found',
    default => throw new UnexpectedValueException("Unknown HTTP code: {$code}"),
};

// Arrow function
$validate = fn(string $email): string => filter_var($email, FILTER_VALIDATE_EMAIL)
    ? $email
    : throw new InvalidArgumentException("Invalid email: {$email}");

echo $validate('user@domain.com');

// Null coalescing
$config = ['host' => 'localhost'];
$port = $config['port'] ?? throw new RuntimeException('Port config is missing');

// === Practical Example — Validation in Constructor ===

class Email
{
    public function __construct(private string $value)
    {
        filter_var($this->value, FILTER_VALIDATE_EMAIL)
            ?: throw new InvalidArgumentException("Invalid email: {$this->value}");
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

// $email = new Email('invalid'); // InvalidArgumentException
$email = new Email('user@domain.com');
echo $email; // user@domain.com
