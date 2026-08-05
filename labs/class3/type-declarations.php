<?php

function add(int $a, int $b): int
{
    return $a + $b;
}

echo add(5, 3);
echo add(5, "3");
// add(5, "abc");  // TypeError if strict_types=1


class Product
{
    public string $name;
    public float $price;
    public int $stock;
    public bool $available;

    public function __construct(
        string $name,
        float  $price,
        int    $stock = 0
    )
    {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->available = $stock > 0;
    }
}

class Product
{
    public function __construct(
        public string $name,
        public float  $price,
        public int    $stock = 0,
        public bool   $available = false,
    )
    {
        $this->available = $stock > 0;
    }
}