<?php

// === 10 — Class Constants ===

class StatusOrder
{
    public const string PENDING     = 'pending';
    public const string PROCESSING  = 'processing';
    public const string SHIPPED     = 'shipped';
    public const string DELIVERED   = 'delivered';
    public const string CANCELLED   = 'cancelled';

    private const array FINAL_STATUSES = [
        self::DELIVERED,
        self::CANCELLED,
    ];

    public static function isFinal(string $status): bool
    {
        return in_array($status, self::FINAL_STATUSES, true);
    }
}

$status = StatusOrder::PROCESSING;
var_dump(StatusOrder::isFinal($status)); // bool(false)
var_dump(StatusOrder::isFinal(StatusOrder::DELIVERED)); // bool(true)

// === final const (PHP 8.1+) ===

class ConfigBase
{
    final public const string VERSION = '1.0.0';
    public const string ENVIRONMENT = 'dev';
}

class ProductionConfig extends ConfigBase
{
    // public const string VERSION = '2.0.0'; // Error: cannot override final const
    public const string ENVIRONMENT = 'production'; // OK
}
