<?php

// === 01 — Classes and Objects ===

class Product
{
    public string $name;
    public float $price;
}

$product1 = new Product();
$product1->name  = 'Notebook';
$product1->price = 3500.00;

$product2 = new Product();
$product2->name  = 'Mouse';
$product2->price = 89.90;

echo "{$product1->name}: $ {$product1->price}"; // Notebook: $ 3500

// === $this — Reference to Current Object ===

class Message
{
    public string $text;

    public function display(): void
    {
        echo $this->text;
    }
}

$message = new Message();
$message->text = 'Hello, world!';
$message->display(); // Hello, world!
