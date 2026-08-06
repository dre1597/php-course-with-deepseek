<?php

// === 09 — Static Properties and Methods ===

class Counter
{
    private static int $total = 0;

    public function __construct()
    {
        self::$total++;
    }

    public static function getTotal(): int
    {
        return self::$total;
    }

    public static function reset(): void
    {
        self::$total = 0;
    }
}

new Counter();
new Counter();
new Counter();

echo Counter::getTotal(); // 3

Counter::reset();
echo Counter::getTotal(); // 0

// === self:: vs static:: (Late Static Binding) ===

class ParentClass
{
    public static function who(): string
    {
        return self::class;
    }

    public static function whoReal(): string
    {
        return static::class;
    }
}

class ChildClass extends ParentClass {}

echo ChildClass::who();      // ParentClass   (self:: binds at compile time)
echo ChildClass::whoReal();  // ChildClass  (static:: binds at runtime)
