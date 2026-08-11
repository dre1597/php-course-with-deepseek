<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ShoppingCart.php';

class ShoppingCartTest extends TestCase
{
    public function testConstructorStartsWithEmptyCart(): void
    {
        $cart = new ShoppingCart();

        $this->assertEmpty($cart->getItems());
        $this->assertNull($cart->getCoupon());
    }

    public function testConstructorAcceptsPreloadedItems(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);

        $this->assertCount(1, $cart->getItems());
    }

    public function testConstructorAcceptsPreloadedCoupon(): void
    {
        $cart = new ShoppingCart([], 'WELCOME10');

        $this->assertSame('WELCOME10', $cart->getCoupon());
    }

    public function testAddItemToEmptyCart(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'Notebook', 3500);

        $items = $cart->getItems();

        $this->assertCount(1, $items);
        $this->assertSame('Notebook', $items['p1']['name']);
        $this->assertSame(3500.0, $items['p1']['price']);
        $this->assertSame(1, $items['p1']['quantity']);
    }

    public function testAddItemWithPositiveQuantity(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'Mouse', 50, 3);

        $items = $cart->getItems();

        $this->assertSame(3, $items['p1']['quantity']);
    }

    public function testAddDuplicateItemMergesQuantity(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'Mouse', 50, 2);
        $cart->addItem('p1', 'Mouse', 50, 3);

        $this->assertSame(5, $cart->getItems()['p1']['quantity']);
    }

    public function testAddItemWithQtyZeroDoesNothing(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 10, 0);

        $this->assertEmpty($cart->getItems());
    }

    public function testAddItemWithNegativeQtyDoesNothing(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 10, -1);

        $this->assertEmpty($cart->getItems());
    }

    public function testIncrementExistingItem(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);
        $cart->incrementQuantity('p1', 3);

        $this->assertSame(5, $cart->getItems()['p1']['quantity']);
    }

    public function testIncrementNonExistentItemDoesNothing(): void
    {
        $cart = new ShoppingCart();
        $cart->incrementQuantity('ghost');

        $this->assertEmpty($cart->getItems());
    }

    public function testIncrementWithNegativeAmountDoesNothing(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);
        $cart->incrementQuantity('p1', -1);

        $this->assertSame(2, $cart->getItems()['p1']['quantity']);
    }

    public function testIncrementWithZeroDoesNothing(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);
        $cart->incrementQuantity('p1', 0);

        $this->assertSame(2, $cart->getItems()['p1']['quantity']);
    }

    public function testDecrementExistingItem(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 5]]);
        $cart->decrementQuantity('p1', 2);

        $this->assertSame(3, $cart->getItems()['p1']['quantity']);
    }

    public function testDecrementToZeroRemovesItem(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 3]]);
        $cart->decrementQuantity('p1', 3);

        $this->assertFalse(isset($cart->getItems()['p1']));
    }

    public function testDecrementBelowZeroRemovesItem(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);
        $cart->decrementQuantity('p1', 5);

        $this->assertFalse(isset($cart->getItems()['p1']));
    }

    public function testDecrementNonExistentItemDoesNothing(): void
    {
        $cart = new ShoppingCart();
        $cart->decrementQuantity('ghost');

        $this->assertEmpty($cart->getItems());
    }

    public function testDecrementWithNegativeAmountDoesNothing(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 2]]);
        $cart->decrementQuantity('p1', -1);

        $this->assertSame(2, $cart->getItems()['p1']['quantity']);
    }

    public function testRemoveExistingItem(): void
    {
        $cart = new ShoppingCart(['p1' => ['name' => 'A', 'price' => 10, 'quantity' => 1]]);
        $cart->removeItem('p1');

        $this->assertEmpty($cart->getItems());
    }

    public function testRemoveNonExistentItemDoesNothing(): void
    {
        $cart = new ShoppingCart();
        $cart->removeItem('ghost');

        $this->assertEmpty($cart->getItems());
    }

    public function testApplyPercentCoupon(): void
    {
        $cart = new ShoppingCart();
        $cart->applyCoupon('WELCOME10');

        $this->assertSame('WELCOME10', $cart->getCoupon());
    }

    public function testApplyFixedCoupon(): void
    {
        $cart = new ShoppingCart();
        $cart->applyCoupon('SAVE20');

        $this->assertSame('SAVE20', $cart->getCoupon());
    }

    public function testApplyCouponIsCaseInsensitive(): void
    {
        $cart = new ShoppingCart();
        $result = $cart->applyCoupon('welcome10');

        $this->assertTrue($result);
        $this->assertSame('WELCOME10', $cart->getCoupon());
    }

    public function testApplyInvalidCouponReturnsFalse(): void
    {
        $cart = new ShoppingCart();
        $result = $cart->applyCoupon('INVALID');

        $this->assertFalse($result);
        $this->assertNull($cart->getCoupon());
    }

    public function testRemoveCoupon(): void
    {
        $cart = new ShoppingCart([], 'WELCOME10');
        $cart->removeCoupon();

        $this->assertNull($cart->getCoupon());
    }

    public function testSubtotalSingleItem(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 25.50);

        $this->assertSame(25.50, $cart->getSubtotal());
    }

    public function testSubtotalMultipleItemsWithQuantities(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'A', 10, 3);
        $cart->addItem('p2', 'B', 20, 2);

        $this->assertSame(70.0, $cart->getSubtotal());
    }

    public function testSubtotalEmptyCartIsZero(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame(0.0, $cart->getSubtotal());
    }

    public function testPercentDiscount(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 100);
        $cart->applyCoupon('WELCOME10');

        $this->assertSame(10.0, $cart->getDiscount());
    }

    public function testFixedDiscount(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 100);
        $cart->applyCoupon('SAVE20');

        $this->assertSame(20.0, $cart->getDiscount());
    }

    public function testFixedDiscountCapsAtSubtotal(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 10);
        $cart->applyCoupon('SAVE20');

        $this->assertSame(10.0, $cart->getDiscount());
    }

    public function testNoCouponMeansZeroDiscount(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 100);

        $this->assertSame(0.0, $cart->getDiscount());
    }

    public function testShippingUpTo50(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 50);

        $this->assertSame(15.0, $cart->getShipping());
    }

    public function testShippingBetween50And150(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 100);

        $this->assertSame(10.0, $cart->getShipping());
    }

    public function testShippingAbove150IsFree(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 200);

        $this->assertSame(0.0, $cart->getShipping());
    }

    public function testShippingBoundaryAt50(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 50.0);

        $this->assertSame(15.0, $cart->getShipping());
    }

    public function testShippingBoundaryAt50Point01(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 50.01);

        $this->assertSame(10.0, $cart->getShipping());
    }

    public function testShippingBoundaryAt150(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 150.0);

        $this->assertSame(10.0, $cart->getShipping());
    }

    public function testTotalEqualsSubtotalMinusDiscountPlusShipping(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 200);
        $cart->applyCoupon('WELCOME10');

        $this->assertSame(180.0, $cart->getTotal());
    }

    public function testTotalWithFixedDiscountAndShipping(): void
    {
        $cart = new ShoppingCart();
        $cart->addItem('p1', 'X', 80);
        $cart->applyCoupon('SAVE20');

        $this->assertSame(70.0, $cart->getTotal());
    }

    public function testEmptyCartTotalIsZero(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame(0.0, $cart->getTotal());
        $this->assertSame(0.0, $cart->getSubtotal());
        $this->assertSame(0.0, $cart->getDiscount());
        $this->assertSame(0.0, $cart->getShipping());
    }
}
