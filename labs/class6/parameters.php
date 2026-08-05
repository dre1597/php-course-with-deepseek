<?php

function createUser(string $name, int $age = 18, bool $active = true): array
{
    return [
        'name' => $name,
        'age' => $age,
        'active' => $active,
    ];
}

// Named arguments usage
$user1 = createUser('John');                  // age=18, active=true
$user2 = createUser('Mary', 25);               // age=25, active=true
$user3 = createUser('Peter', 30, false);       // age=30, active=false


function getData(DateTimeInterface $date = new DateTimeImmutable('now')): string
{
    return $date->format('Y-m-d');
}

echo getData(); // 2026-08-04 (today)


function createOrder(
    string $product,
    int    $quantity = 1,
    float  $price = 0.0,
    string $client = 'Anonymous',
): array
{
    return compact('product', 'quantity', 'price', 'client');
}

// Chamadas com named arguments
$order1 = createOrder(
    product: 'Laptop',
    price: 3500.00,
    client: 'Anna',
    quantity: 2,
);

$order2 = createOrder(price: 99.90, product: 'Mouse');

print_r($order1);
/*
Array
(
    [product] => Laptop
    [quantity] => 2
    [price] => 3500
    [client] => Anna
)
*/
