<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/products_crud.php';

class ProductsCrudTest extends TestCase
{
    private ProductsCrud $productsCrud;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->productsCrud = new ProductsCrud($pdo);
    }

    public function testCreateInsertsProduct(): void
    {
        $this->productsCrud->create('Pen', 2.50, 100);

        $products = $this->productsCrud->findAll();

        $this->assertCount(1, $products);
        $this->assertSame('Pen', $products[0]->name);
        $this->assertSame(2.50, $products[0]->price);
        $this->assertSame(100, $products[0]->quantity);
    }

    public function testFindAllReturnsMultipleProducts(): void
    {
        $this->productsCrud->create('Mouse', 89.90, 15);
        $this->productsCrud->create('Keyboard', 149.90, 8);
        $this->productsCrud->create('Monitor', 899.90, 3);

        $products = $this->productsCrud->findAll();

        $this->assertCount(3, $products);
    }

    public function testFindOneReturnsProductById(): void
    {
        $this->productsCrud->create('Laptop', 3500.00, 5);
        $this->productsCrud->create('Tablet', 1200.00, 10);

        $products = $this->productsCrud->findAll();
        $product   = $this->productsCrud->findOne($products[0]->id);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('Laptop', $product->name);
        $this->assertSame(3500.00, $product->price);
    }

    public function testUpdateModifiesProduct(): void
    {
        $this->productsCrud->create('Notebook', 15.00, 50);

        $products = $this->productsCrud->findAll();
        $productId = $products[0]->id;

        $this->productsCrud->update($productId, 'Spiral Notebook', 19.90, 30);

        $updatedProduct = $this->productsCrud->findOne($productId);
        $this->assertSame('Spiral Notebook', $updatedProduct->name);
        $this->assertSame(19.90, $updatedProduct->price);
        $this->assertSame(30, $updatedProduct->quantity);
    }

    public function testDeleteRemovesProduct(): void
    {
        $this->productsCrud->create('Eraser', 3.50, 200);
        $this->productsCrud->create('Sharpener', 5.00, 150);

        $products = $this->productsCrud->findAll();
        $this->assertCount(2, $products);

        $firstProductId = $products[0]->id;
        $this->productsCrud->delete($firstProductId);

        $productsAfterDelete = $this->productsCrud->findAll();
        $this->assertCount(1, $productsAfterDelete);
    }

    public function testProductsHaveTimestamps(): void
    {
        $this->productsCrud->create('Pencil', 1.50, 500);

        $product = $this->productsCrud->findAll()[0];

        $this->assertNotEmpty($product->created_at);
    }

    public function testFindAllReturnsEmptyArrayWhenNoProducts(): void
    {
        $products = $this->productsCrud->findAll();

        $this->assertIsArray($products);
        $this->assertEmpty($products);
    }

    public function testCreatedProductHasAutoIncrementId(): void
    {
        $this->productsCrud->create('Product A', 10.00, 1);
        $this->productsCrud->create('Product B', 20.00, 2);

        $products = $this->productsCrud->findAll();

        $this->assertSame(1, $products[0]->id);
        $this->assertSame(2, $products[1]->id);
    }

    public function testCreateProductWithZeroPrice(): void
    {
        $this->productsCrud->create('Freebie', 0.00, 10);

        $products = $this->productsCrud->findAll();

        $this->assertCount(1, $products);
        $this->assertSame(0.00, $products[0]->price);
    }

    public function testCreateProductWithZeroQuantity(): void
    {
        $this->productsCrud->create('Out of Stock', 99.90, 0);

        $products = $this->productsCrud->findAll();

        $this->assertCount(1, $products);
        $this->assertSame(0, $products[0]->quantity);
    }

    public function testCreateProductWithVeryLongName(): void
    {
        $longName = str_repeat('A', 255);

        $this->productsCrud->create($longName, 10.00, 1);

        $products = $this->productsCrud->findAll();
        $product  = $this->productsCrud->findOne($products[0]->id);
        $this->assertSame($longName, $product->name);
    }

    public function testCreateProductWithSpecialCharactersInName(): void
    {
        $this->productsCrud->create("Café & Chá — 100% natural", 25.90, 30);

        $products = $this->productsCrud->findAll();
        $product  = $this->productsCrud->findOne($products[0]->id);

        $this->assertSame("Café & Chá — 100% natural", $product->name);
    }

    public function testCreateProductWithFloatPriceDecimals(): void
    {
        $this->productsCrud->create('Precision Item', 19.999999, 1);

        $products = $this->productsCrud->findAll();
        $product  = $this->productsCrud->findOne($products[0]->id);

        $this->assertEqualsWithDelta(19.999999, $product->price, 0.000001);
    }

    public function testUpdateNonExistentProductDoesNothing(): void
    {
        $this->productsCrud->create('Real Product', 50.00, 10);

        $this->productsCrud->update(999, 'Ghost', 0.00, 0);

        $products = $this->productsCrud->findAll();
        $product  = $this->productsCrud->findOne($products[0]->id);
        $this->assertSame('Real Product', $product->name);
        $this->assertSame(50.00, $product->price);
        $this->assertSame(10, $product->quantity);
    }

    public function testDeleteNonExistentProductDoesNothing(): void
    {
        $this->productsCrud->create('Only Item', 10.00, 5);

        $this->productsCrud->delete(999);

        $products = $this->productsCrud->findAll();
        $this->assertCount(1, $products);
    }

    public function testUpdateProductWithSameValues(): void
    {
        $this->productsCrud->create('Static', 42.00, 7);

        $products = $this->productsCrud->findAll();
        $productId = $products[0]->id;

        $this->productsCrud->update($productId, 'Static', 42.00, 7);

        $updatedProduct = $this->productsCrud->findOne($productId);
        $this->assertSame('Static', $updatedProduct->name);
        $this->assertSame(42.00, $updatedProduct->price);
        $this->assertSame(7, $updatedProduct->quantity);
    }

    public function testDeleteLastProductLeavesEmptyTable(): void
    {
        $this->productsCrud->create('Solo', 1.00, 1);

        $products = $this->productsCrud->findAll();
        $this->productsCrud->delete($products[0]->id);

        $this->assertEmpty($this->productsCrud->findAll());
    }

    public function testAutoIncrementContinuesAfterDelete(): void
    {
        $this->productsCrud->create('First', 1.00, 1);
        $this->productsCrud->create('Second', 2.00, 2);

        $products = $this->productsCrud->findAll();
        $this->productsCrud->delete($products[1]->id);

        $this->productsCrud->create('Third', 3.00, 3);

        $allProducts = $this->productsCrud->findAll();
        $ids = array_map(fn(Product $p): int => $p->id, $allProducts);
        sort($ids);

        $this->assertSame([1, 3], $ids);
    }

    public function testCreateManyProducts(): void
    {
        $count = 100;
        for ($i = 1; $i <= $count; $i++) {
            $this->productsCrud->create("Product {$i}", $i * 1.50, $i * 10);
        }

        $products = $this->productsCrud->findAll();

        $this->assertCount($count, $products);
        $this->assertSame('Product 1', $products[0]->name);
        $this->assertSame(1.50, $products[0]->price);
        $this->assertSame(10, $products[0]->quantity);
        $this->assertSame("Product {$count}", $products[$count - 1]->name);
    }

    public function testCreateUpdateDeleteCycle(): void
    {
        $this->productsCrud->create('Cycle Item', 100.00, 50);

        $products = $this->productsCrud->findAll();
        $productId = $products[0]->id;

        $this->productsCrud->update($productId, 'Updated Cycle', 200.00, 25);

        $updated = $this->productsCrud->findOne($productId);
        $this->assertSame('Updated Cycle', $updated->name);

        $this->productsCrud->delete($updated->id);
        $this->assertEmpty($this->productsCrud->findAll());
    }

    public function testCreateWithZeroQuantityFollowedByUpdate(): void
    {
        $this->productsCrud->create('Restock Item', 15.00, 0);

        $products = $this->productsCrud->findAll();
        $productId = $products[0]->id;

        $this->productsCrud->update($productId, 'Restock Item', 15.00, 100);

        $updated = $this->productsCrud->findOne($productId);
        $this->assertSame(100, $updated->quantity);
    }

    public function testFindAllPreservesInsertionOrder(): void
    {
        $names = ['Zebra', 'Alpha', 'Omega', 'Beta'];
        foreach ($names as $name) {
            $this->productsCrud->create($name, 1.00, 1);
        }

        $products = $this->productsCrud->findAll();

        foreach ($names as $i => $name) {
            $this->assertSame($name, $products[$i]->name);
        }
    }

    public function testProductTypeIntegrity(): void
    {
        $this->productsCrud->create('TypeCheck', 99.99, 42);

        $products = $this->productsCrud->findAll();
        $product  = $this->productsCrud->findOne($products[0]->id);

        $this->assertIsInt($product->id);
        $this->assertIsString($product->name);
        $this->assertIsFloat($product->price);
        $this->assertIsInt($product->quantity);
        $this->assertIsString($product->created_at);
    }

    public function testDeleteAllAndRecreate(): void
    {
        $this->productsCrud->create('Batch 1', 1.00, 1);
        $this->productsCrud->create('Batch 1', 2.00, 2);
        $this->productsCrud->create('Batch 1', 3.00, 3);

        $products = $this->productsCrud->findAll();
        foreach ($products as $p) {
            $this->productsCrud->delete($p->id);
        }

        $this->assertEmpty($this->productsCrud->findAll());

        $this->productsCrud->create('Batch 2', 4.00, 4);
        $this->productsCrud->create('Batch 2', 5.00, 5);

        $products = $this->productsCrud->findAll();
        $this->assertCount(2, $products);
        $this->assertSame(4, $products[0]->id);
        $this->assertSame(5, $products[1]->id);
    }
}
