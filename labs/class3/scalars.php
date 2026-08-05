<?php

// int

$decimal = 42;
$negative = -17;
$binary = 0b1010;      // 10
$octal = 0o755;        // 493
$hex = 0xFF;           // 255
$thousands = 1_000_000;

echo PHP_INT_MAX;  // 9223372036854775807 (64-bit)
echo PHP_INT_MIN;  // -9223372036854775808

// decimal

$simple = 1.5;
$negativeFloat = -0.33;
$scientific = 1.2e3;
$tiny = 7E-10;
$imprecise = 0.1 + 0.2;  // 0.30000000000000004 (IEEE 754 imprecision)

echo PHP_FLOAT_MAX;
echo PHP_FLOAT_MIN;

$result = 0.1 + 0.2;
$expected = 0.3;

if ($result === 0.3) { /* never executes */
}

$epsilon = 0.00001;
if (abs($result - $expected) < $epsilon) {
    echo "Equal (with tolerance)";
}

// bool


$enabled = true;
$disabled = false;

echo true;   // "1"
echo false;  // "" (empty string)

var_dump(true);  // bool(true)
var_dump(false); // bool(false)

$falsy = [
    false,      // boolean false
    0,          // integer zero
    0.0,        // float zero
    -0,         // negative integer zero
    -0.0,       // negative float zero
    '',         // empty string
    '0',        // string "0"
    [],         // empty array
    null,       // null
    // SimpleXML objects created from empty tags
    // (specific internal instances)
];

foreach ($falsy as $value) {
    echo var_export($value, true) . ' → ' . var_export((bool)$value, true) . "\n";
}

// string

// 4 formas de declarar strings:
$s1 = 'Single quotes';       // No interpolation, no escapes (except \\ and \')
$s2 = "Double quotes";       // Interpolates variables, interprets escapes
$s3 = <<<EOT
Heredoc: works like double quotes
EOT;
$s4 = <<<'EOT'
Nowdoc: works like single quotes
EOT;

$name = "Charles";

echo 'Hello, $name!\n';

echo "Hello, $name!\n";

// string interpolation

$fruit = "apple";
$quantity = 5;

echo "I have $quantity $fruit(s).";

echo "I have {$quantity} {$fruit}(s).";

$product = ['name' => 'Pen', 'price' => 2.50];
echo "Product: {$product['name']} costs \$ {$product['price']}";

class Item {
    public string $name = 'Notebook';
}
$item = new Item();
echo "Item: {$item->name}";

// Expressions are NOT allowed in interpolation
$total = $quantity * 2.50;
echo "Total: {$total}";

// Or use concatenation
echo "Total: " . ($quantity * 2.50);

// Heredoc

$name = "World";
$version = PHP_VERSION;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Hello, {$name}!</title>
</head>
<body>
    <h1>Welcome, {$name}</h1>
    <p>Running PHP {$version}</p>
</body>
</html>
HTML;

echo $html;

// Heredoc with flexible indentation (PHP 7.3+)
// The closing marker determines the base indentation level

function generateEmail(): string
{
    $user = "John";

    return <<<TEMPLATE
        Hello, {$user}!

        Your order has been confirmed.

        Sincerely,
        Team
        TEMPLATE;
}

echo generateEmail();

// nowdoc

$name = "Mary";

$text = <<<'TEXT'
Hello, $name!
No variable interpolation here.
No escapes either: \n \t
TEXT;

echo $text;
