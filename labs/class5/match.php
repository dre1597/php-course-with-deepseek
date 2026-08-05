<?php

$statusCode = 404;

$message = match ($statusCode) {
    200     => 'OK',
    201     => 'Created',
    301     => 'Moved Permanently',
    404     => 'Not Found',
    500     => 'Internal Server Error',
    default => 'Unknown Status',
};

echo $message; // Not Found


$day = 'saturday';

$dayType = match ($day) {
    'monday', 'tuesday', 'wednesday', 'thursday', 'friday' => 'Weekday',
    'saturday', 'sunday' => 'Weekend',
    default => 'Invalid day',
};

echo $dayType; // Weekend


$value = 150.00;
$discountType = 'blackfriday';

$finalPrice = match ($discountType) {
    'vip' => $value * 0.7,           // 30% off
    'blackfriday' => $value * 0.5,           // 50% off
    'subscriber' => $value * 0.85,          // 15% off
    default => $value,                 // full price
};

echo "Original price: R$ " . number_format($value, 2, ',', '.') . "\n";
echo "Final price: R$ " . number_format($finalPrice, 2, ',', '.') . "\n";
// Original price: R$ 150,00
// Final price: R$ 75,00


enum PaymentMethod: string
{
    case Pix = 'pix';
    case BankSlip = 'bank_slip';
    case Credit = 'credit';
    case Debit = 'debit';
}

function getProcessingFee(PaymentMethod $method): float
{
    return match ($method) {
        PaymentMethod::Pix => 0.00,   // no fee
        PaymentMethod::BankSlip => 3.50,   // flat fee
        PaymentMethod::Credit => 0.0499, // 4.99%
        PaymentMethod::Debit => 0.0299, // 2.99%
    };
    // No default needed: enum covers all cases.
    // If a new case is added to the enum, PHP will throw
    // an UnhandledMatchError at runtime.
}

echo getProcessingFee(PaymentMethod::Pix); // 0.0


$grade = 8.5;
$attendance = 85;

$result = match (true) {
    $grade >= 9 && $attendance >= 90 => 'Approved with honors',
    $grade >= 7 && $attendance >= 75 => 'Approved',
    $grade >= 5 && $attendance >= 75 => 'Recovery exam',
    $attendance < 75 => 'Failed due to absences',
    default => 'Failed by grade',
};

echo $result; // Approved


$direction = 'north';

try {
    $command = match ($direction) {
        'up' => 'Move up',
        'down' => 'Move down',
        'left' => 'Move left',
        'right' => 'Move right',
    };
} catch (\UnhandledMatchError $e) {
    echo "Direction '{$direction}' not recognized.";
}
// Direction 'north' not recognized.
