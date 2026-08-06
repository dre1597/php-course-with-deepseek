<?php

// === 17 — Late Static Binding (static:: vs self::) ===

abstract class Repository
{
    protected static string $table;

    public static function find(int $id): ?static
    {
        $table = static::$table;  // LSB: resolves on subclass
        echo "SELECT * FROM {$table} WHERE id = {$id}" . PHP_EOL;
        return $id > 0 ? new static() : null; // LSB: instantiates the subclass
    }

    public static function table(): string
    {
        return static::$table;
    }
}

class UserRepository extends Repository
{
    protected static string $table = 'users';
}

class OrderRepository extends Repository
{
    protected static string $table = 'orders';
}

$user = UserRepository::find(1);
// SELECT * FROM users WHERE id = 1

$order = OrderRepository::find(42);
// SELECT * FROM orders WHERE id = 42

echo UserRepository::table(); // users
echo OrderRepository::table();  // orders

// Without static::, self::$table would always return Repository's value (which is not set).
