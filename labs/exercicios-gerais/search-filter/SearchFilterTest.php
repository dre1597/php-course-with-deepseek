<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/search_filter.php';

class SearchFilterTest extends TestCase
{
    private SearchFilter $searchFilter;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->searchFilter = new SearchFilter($pdo);

        $this->seedProducts($pdo);
    }

    private function seedProducts(PDO $pdo): void
    {
        $products = [
            ['Laptop Gamer',       4500.00,   5],
            ['Mouse Wireless',      149.90,  30],
            ['Teclado Mecânico',    349.90,  15],
            ['Monitor 27"',        1899.00,   8],
            ['Mousepad',             29.90, 100],
            ['Cadeira Ergonômica', 2100.00,   3],
            ['Webcam HD',           199.90,  20],
            ['Hub USB-C',           89.90,   50],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO products (name, price, quantity, created_at) VALUES (:name, :price, :quantity, :created_at)'
        );

        foreach ($products as $i => [$name, $price, $quantity]) {
            $stmt->execute([
                ':name'       => $name,
                ':price'      => $price,
                ':quantity'   => $quantity,
                ':created_at' => date('Y-m-d H:i:s', strtotime("2026-08-01 +{$i} days")),
            ]);
        }
    }

    public function testSearchByNameReturnsPartialMatches(): void
    {
        $results = $this->searchFilter->search(name: 'Mouse');

        $this->assertCount(2, $results);
        $names = array_map(fn(Product $p): string => $p->name, $results);
        $this->assertContains('Mouse Wireless', $names);
        $this->assertContains('Mousepad', $names);
    }

    public function testSearchByNameIsCaseInsensitive(): void
    {
        $results = $this->searchFilter->search(name: 'monitor');

        $this->assertCount(1, $results);
        $this->assertSame('Monitor 27"', $results[0]->name);
    }

    public function testSearchByPriceRangeReturnsProductsWithinBounds(): void
    {
        $results = $this->searchFilter->search(minPrice: 100.00, maxPrice: 200.00);

        $this->assertCount(2, $results);
        $names = array_map(fn(Product $p): string => $p->name, $results);
        $this->assertContains('Mouse Wireless', $names);
        $this->assertContains('Webcam HD', $names);
    }

    public function testSearchByNameAndPriceRangeCombined(): void
    {
        $results = $this->searchFilter->search(name: 'm', minPrice: 200.00, maxPrice: 500.00);

        $names = array_map(fn(Product $p): string => $p->name, $results);
        $this->assertContains('Teclado Mecânico', $names);
        $this->assertNotContains('Mouse Wireless', $names);
    }

    public function testSearchWithNoFiltersReturnsAllProducts(): void
    {
        $results = $this->searchFilter->search();

        $this->assertCount(8, $results);
    }

    public function testSearchDefaultsToOrderByIdAscending(): void
    {
        $results = $this->searchFilter->search();

        $ids = array_map(fn(Product $p): int => $p->id, $results);
        $sorted = $ids;
        sort($sorted);

        $this->assertSame($sorted, $ids);
    }

    public function testSearchWithOnlyMinPrice(): void
    {
        $results = $this->searchFilter->search(minPrice: 4500.00);

        $this->assertCount(1, $results);
        $this->assertSame('Laptop Gamer', $results[0]->name);
    }

    public function testSearchWithOnlyMaxPrice(): void
    {
        $results = $this->searchFilter->search(maxPrice: 30.00);

        $this->assertCount(1, $results);
        $this->assertSame('Mousepad', $results[0]->name);
    }

    public function testSearchOrderedByPriceAscending(): void
    {
        $results = $this->searchFilter->search(orderBy: 'price', direction: 'ASC');

        $prices = array_map(fn(Product $p): float => $p->price, $results);
        $sorted  = $prices;
        sort($sorted);

        $this->assertSame($sorted, $prices);
        $this->assertSame(29.90, $results[0]->price);
        $this->assertSame(4500.00, $results[7]->price);
    }

    public function testSearchOrderedByPriceDescending(): void
    {
        $results = $this->searchFilter->search(orderBy: 'price', direction: 'DESC');

        $this->assertSame(4500.00, $results[0]->price);
        $this->assertSame(29.90, $results[7]->price);
    }

    public function testSearchOrderedByNameAscending(): void
    {
        $results = $this->searchFilter->search(orderBy: 'name', direction: 'ASC');

        $this->assertSame('Cadeira Ergonômica', $results[0]->name);
    }

    public function testSearchOrderedByCreatedAt(): void
    {
        $results = $this->searchFilter->search(orderBy: 'created_at', direction: 'ASC');

        $this->assertSame('Laptop Gamer', $results[0]->name);
        $this->assertSame('Hub USB-C', $results[7]->name);
    }

    public function testSearchWithNoMatchingNameReturnsEmptyArray(): void
    {
        $results = $this->searchFilter->search(name: 'XYZNotFound');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testSearchWithMinPriceGreaterThanMaxReturnsEmpty(): void
    {
        $results = $this->searchFilter->search(minPrice: 500.00, maxPrice: 100.00);

        $this->assertEmpty($results);
    }

    public function testSearchWithEmptyNameReturnsAll(): void
    {
        $results = $this->searchFilter->search(name: '');

        $this->assertCount(8, $results);
    }

    public function testSearchWithPriceRangeIncludesExactBoundary(): void
    {
        $results = $this->searchFilter->search(minPrice: 29.90, maxPrice: 29.90);

        $this->assertCount(1, $results);
        $this->assertSame('Mousepad', $results[0]->name);
    }

    public function testSearchResultProductsHaveAllFields(): void
    {
        $results = $this->searchFilter->search(name: 'Laptop Gamer');

        $this->assertInstanceOf(Product::class, $results[0]);
        $this->assertIsInt($results[0]->id);
        $this->assertIsString($results[0]->name);
        $this->assertIsFloat($results[0]->price);
        $this->assertIsInt($results[0]->quantity);
        $this->assertIsString($results[0]->created_at);
    }

    public function testInvalidOrderByFallsBackToId(): void
    {
        $results = $this->searchFilter->search(orderBy: 'nonexistent_column');

        $ids = array_map(fn(Product $p): int => $p->id, $results);
        $sorted = $ids;
        sort($sorted);

        $this->assertSame($sorted, $ids);
    }

    public function testInvalidDirectionFallsBackToAsc(): void
    {
        $results = $this->searchFilter->search(orderBy: 'price', direction: 'WHATEVER');

        $this->assertSame(29.90, $results[0]->price);
    }

    public function testNullNameFilterDoesNotAffectResults(): void
    {
        $results = $this->searchFilter->search(name: null);

        $this->assertCount(8, $results);
    }

    public function testNullMinPriceDoesNotAffectResults(): void
    {
        $results = $this->searchFilter->search(minPrice: null);

        $this->assertCount(8, $results);
    }

    public function testNullMaxPriceDoesNotAffectResults(): void
    {
        $results = $this->searchFilter->search(maxPrice: null);

        $this->assertCount(8, $results);
    }

    public function testOnlyNameFilterWorksIndependently(): void
    {
        $results = $this->searchFilter->search(name: 'Cadeira');

        $this->assertCount(1, $results);
        $this->assertSame('Cadeira Ergonômica', $results[0]->name);
    }

    public function testOnlyMinPriceFilterWorksIndependently(): void
    {
        $results = $this->searchFilter->search(minPrice: 2000.00);

        $this->assertCount(2, $results);
    }

    public function testOnlyMaxPriceFilterWorksIndependently(): void
    {
        $results = $this->searchFilter->search(maxPrice: 50.00);

        $this->assertCount(1, $results);
        $this->assertSame('Mousepad', $results[0]->name);
    }

    public function testNameAndMinPriceOnly(): void
    {
        $results = $this->searchFilter->search(name: 'm', minPrice: 200.00);

        $names = array_map(fn(Product $p): string => $p->name, $results);
        $this->assertContains('Teclado Mecânico', $names);
        $this->assertContains('Monitor 27"', $names);
        $this->assertContains('Cadeira Ergonômica', $names);
    }

    public function testNameAndMaxPriceOnly(): void
    {
        $results = $this->searchFilter->search(name: 'mouse', maxPrice: 100.00);

        $this->assertCount(1, $results);
        $this->assertSame('Mousepad', $results[0]->name);
    }
}
