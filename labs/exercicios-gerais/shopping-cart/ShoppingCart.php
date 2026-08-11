<?php

class ShoppingCart
{
    public const array COUPONS = [
        'WELCOME10' => ['type' => 'percent', 'value' => 10],
        'SAVE20' => ['type' => 'fixed', 'value' => 20],
    ];

    public const array SHIPPING_TIERS = [
        ['max' => 50, 'cost' => 15],
        ['max' => 150, 'cost' => 10],
        ['max' => INF, 'cost' => 0],
    ];

    private array $items;
    private ?string $coupon;

    public function __construct(array $items = [], ?string $coupon = null)
    {
        $this->items = $items;
        $this->coupon = $coupon;
    }

    public function addItem(string $id, string $name, float $price, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            return;
        }

        if (isset($this->items[$id])) {
            $this->items[$id]['quantity'] += $quantity;
            return;
        }

        $this->items[$id] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    public function incrementQuantity(string $id, int $amount = 1): void
    {
        if ($amount <= 0 || !isset($this->items[$id])) {
            return;
        }

        $this->items[$id]['quantity'] += $amount;
    }

    public function decrementQuantity(string $id, int $amount = 1): void
    {
        if ($amount <= 0 || !isset($this->items[$id])) {
            return;
        }

        $this->items[$id]['quantity'] -= $amount;

        if ($this->items[$id]['quantity'] <= 0) {
            unset($this->items[$id]);
        }
    }

    public function removeItem(string $id): void
    {
        unset($this->items[$id]);
    }

    public function applyCoupon(string $code): bool
    {
        if (!isset(self::COUPONS[strtoupper($code)])) {
            return false;
        }

        $this->coupon = strtoupper($code);
        return true;
    }

    public function removeCoupon(): void
    {
        $this->coupon = null;
    }

    public function getSubtotal(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return round($total, 2);
    }

    public function getDiscount(): float
    {
        if ($this->coupon === null) {
            return 0.0;
        }

        $info = self::COUPONS[$this->coupon];
        $sub = $this->getSubtotal();

        if ($info['type'] === 'percent') {
            return round($sub * ($info['value'] / 100), 2);
        }

        return min($info['value'], $sub);
    }

    public function getShipping(): float
    {
        $sub = $this->getSubtotal();

        if ($sub <= 0) {
            return 0;
        }

        foreach (self::SHIPPING_TIERS as $tier) {
            if ($sub <= $tier['max']) {
                return $tier['cost'];
            }
        }

        return 0;
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() - $this->getDiscount() + $this->getShipping();
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCoupon(): ?string
    {
        return $this->coupon;
    }
}
