<?php

// pure enum

enum OrderStatus
{
    case Pending;
    case Paid;
    case Shipped;
    case Delivered;
    case Cancelled;
}

function updateStatus(OrderStatus $status): void
{
    echo "Status changed to: " . $status->name . "\n";
}

updateStatus(OrderStatus::Paid);
updateStatus(OrderStatus::Delivered);

// backed enum

enum ShirtSize: string
{
    case PP = 'pp';
    case P = 'p';
    case M = 'm';
    case G = 'g';
    case GG = 'gg';
    case XG = 'xg';
}

function selectSize(ShirtSize $size): void
{
    echo "Size: " . $size->value . "\n";
}

selectSize(ShirtSize::M);

// A partir de um valor:
$size = ShirtSize::from('g');
echo $size->name;

// from() throws ValueError if value doesn't exist:
// ShirtSize::from('xxl'); // ValueError

// tryFrom() returns null if not found:
$attempt = ShirtSize::tryFrom('xxl');
var_dump($attempt);

// int enums

enum ErrorCode: int
{
    case NotFound = 404;
    case Unauthorized = 401;
    case InternalError = 500;
    case ValidationFailed = 422;
}

echo ErrorCode::NotFound->value; // 404

// enum methods

enum PaymentMethod: string
{
    case CreditCard = 'credit_card';
    case BankSlip = 'bank_slip';
    case Pix = 'pix';
    case Debit = 'debit_card';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard => 'Credit Card',
            self::BankSlip => 'Bank Slip',
            self::Pix => 'PIX',
            self::Debit => 'Debit Card',
        };
    }

    public function processingDeadline(): string
    {
        return match ($this) {
            self::Pix => 'Instant',
            self::CreditCard => 'Up to 24h',
            self::Debit => 'Up to 24h',
            self::BankSlip => 'Up to 3 business days',
        };
    }
}

$method = PaymentMethod::Pix;
echo $method->label();
echo $method->processingDeadline();

// Iterate over all cases:
foreach (PaymentMethod::cases() as $case) {
    echo "{$case->label()} → {$case->value}\n";
}

// enum with interface and trait

interface Describable
{
    public function describe(): string;
}

trait DefaultDescription
{
    public function describe(): string
    {
        return match (true) {
            $this instanceof OrderStatus => "Order {$this->name}",
            default => $this->name,
        };
    }
}

enum OrderStatusWithDescription: string implements Describable
{
    use DefaultDescription;

    case Pending = 'P';
    case Paid = 'PG';
    case Shipped = 'E';
    case Delivered = 'ET';
}

$status = OrderStatusWithDescription::Pending;
echo $status->describe();
