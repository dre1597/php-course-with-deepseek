<?php

$fruits = ['apple', 'banana', 'orange', 'grape', 'strawberry'];

foreach ($fruits as $fruit) {
    echo "Fruit: {$fruit}\n";
}


$user = [
    'name' => 'John Smith',
    'email' => 'john@example.com',
    'age' => 34,
    'city' => 'New York',
];

foreach ($user as $key => $value) {
    echo ucfirst($key) . ": {$value}\n";
}
// Name: John Smith
// Email: john@example.com
// Age: 34
// City: New York


$products = [
    ['name' => 'Laptop', 'price' => 3500.00, 'stock' => 12],
    ['name' => 'Monitor 27"', 'price' => 1200.00, 'stock' => 5],
    ['name' => 'Keyboard', 'price' => 250.00, 'stock' => 30],
    ['name' => 'Mouse', 'price' => 120.00, 'stock' => 0],
];

echo "┌──────┬────────────────────┬──────────┬─────────┐\n";
echo "│ ID   │ Product            │ Price    │ Stock   │\n";
echo "├──────┼────────────────────┼──────────┼─────────┤\n";

foreach ($products as $id => $product) {
    $idStr = str_pad($id + 1, 4, ' ', STR_PAD_LEFT);
    $nameStr = str_pad($product['name'], 18, ' ');
    $priceStr = 'R$ ' . str_pad(number_format($product['price'], 2, ',', '.'), 7, ' ', STR_PAD_LEFT);
    $stockStr = str_pad($product['stock'], 7, ' ', STR_PAD_LEFT);

    $status = $product['stock'] > 0 ? '' : ' (out of stock)';

    echo "│ {$idStr} │ {$nameStr} │ {$priceStr} │ {$stockStr} │{$status}\n";
}

echo "└──────┴────────────────────┴──────────┴─────────┘\n";


$numbers = [1, 2, 3, 4, 5];

// By value: does NOT modify the original array
foreach ($numbers as $num) {
    $num *= 2; // $num is a copy
}
print_r($numbers); // [1, 2, 3, 4, 5] — unchanged

// By reference: MODIFIES the original array
foreach ($numbers as &$num) {
    $num *= 2;
}
unset($num); // IMPORTANT: break the reference after the loop!
print_r($numbers); // [2, 4, 6, 8, 10]


// Any object implementing Traversable can be used with foreach

class Counter implements \Iterator
{
    private int $position = 0;
    private array $values;

    public function __construct(array $values)
    {
        $this->values = array_values($values);
    }

    public function current(): mixed
    {
        return $this->values[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->values[$this->position]);
    }
}

$counter = new Counter(['a', 'b', 'c']);
foreach ($counter as $index => $value) {
    echo "{$index}: {$value}\n";
}


$colors = ['red', 'green', 'blue'];

foreach ($colors as $index => $color) {
    echo "[{$index}] = {$color}\n";
}
// [0] = red
// [1] = green
// [2] = blue


$text = "PHP";
$characters = mb_str_split($text); // PHP 7.4+

foreach ($characters as $i => $char) {
    echo "Position {$i}: {$char}\n";
}
// Position 0: P
// Position 1: H
// Position 2: P
