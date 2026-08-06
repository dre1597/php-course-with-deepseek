<?php

// === 13 — Property Hooks (PHP 8.4+): get, set, read-only, write-only, in interfaces ===

// Hook get
class User
{
    public string $fullName {
        get => mb_convert_case($this->fullName, MB_CASE_TITLE, 'UTF-8');
    }

    public function __construct(string $name)
    {
        $this->fullName = $name;
    }
}

$user = new User('john doe');
echo $user->fullName; // John Doe

// Hook set
class Product
{
    private float $rawPrice;

    public float $price {
        get => $this->rawPrice;
        set (float $value) {
            if ($value < 0) {
                throw new InvalidArgumentException('Price cannot be negative');
            }
            $this->rawPrice = round($value, 2);
        }
    }

    public function __construct(float $price)
    {
        $this->price = $price;
    }
}

$product = new Product(99.999);
echo $product->price; // 100

// $product->price = -10; // InvalidArgumentException

// Virtual Read-Only Property (get only, no set)
class Rectangle
{
    public function __construct(
        private float $width,
        private float $height,
    ) {}

    public float $area {
        get => $this->width * $this->height;
    } // no set = read-only
}

$rectangle = new Rectangle(10, 5);
echo $rectangle->area; // 50

// $rectangle->area = 60; // Error: property has no set hook

// Write-Only Property (set only, no get)
class Logger
{
    private array $messages = [];

    public string $message {
        set (string $value) {
            $this->messages[] = date('[H:i:s] ') . $value;
        }
    } // no get = write-only

    public function getMessages(): array
    {
        return $this->messages;
    }
}

$logger = new Logger();
$logger->message = 'System started';
$logger->message = 'Processing completed';

print_r($logger->getMessages());
// [[10:30:00] System started, [10:30:05] Processing completed]

// echo $logger->message; // Error: property has no get hook

// Property Hooks in Interfaces
interface Nameable
{
    public string $fullName { get; }
}

class Person implements Nameable
{
    public string $fullName {
        get => $this->firstName . ' ' . $this->lastName;
    }

    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}
