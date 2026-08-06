<?php

// === 15 — Final Property with Constructor Promotion (PHP 8.5+) ===

class Model
{
    public function __construct(
        final public string $table,
    ) {}
}

class User extends Model
{
    // public string $table = 'users'; // Error: final property cannot be overridden
}

// Also works with readonly
abstract class DomainEvent
{
    public function __construct(
        final public readonly string $id,
        final public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}

class UserCreated extends DomainEvent
{
    public function __construct(
        string $id,
        public readonly string $name,
    ) {
        parent::__construct($id);
    }

    // Cannot override $id or $occurredAt
}
