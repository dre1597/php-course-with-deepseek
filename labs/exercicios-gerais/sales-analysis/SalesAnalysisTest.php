<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/sales-analysis.php';


class SalesAnalysisTest extends TestCase
{
    private const SELLERS = ['John', 'Mary', 'Peter', 'Ana', 'Carlos', 'Julia'];
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    private static array $sales;
    private static float $expectedTotal;
    private static string $expectedTopSeller;
    private static string $expectedTopMonth;
    private static float $expectedAverage;

    public static function setUpBeforeClass(): void
    {
        self::$sales = self::generateRandomSales(rand(5, 30));

        $totalsBySeller = [];
        $totalsByMonth = [];
        $sum = 0.0;

        foreach (self::$sales as $sale) {
            $amount = $sale['amount'];
            $sum += $amount;

            $totalsBySeller[$sale['seller']] = ($totalsBySeller[$sale['seller']] ?? 0) + $amount;
            $totalsByMonth[$sale['month']] = ($totalsByMonth[$sale['month']] ?? 0) + $amount;
        }

        self::$expectedTotal = $sum;
        self::$expectedTopSeller = array_search(max($totalsBySeller), $totalsBySeller);
        self::$expectedTopMonth = array_search(max($totalsByMonth), $totalsByMonth);
        self::$expectedAverage = $sum / count(self::$sales);
    }

    private static function generateRandomSales(int $count): array
    {
        $sales = [];

        for ($i = 0; $i < $count; $i++) {
            $sales[] = [
                'seller' => self::SELLERS[array_rand(self::SELLERS)],
                'month' => self::MONTHS[array_rand(self::MONTHS)],
                'amount' => round(mt_rand(1000, 5000) + (mt_rand(0, 99) / 100), 2),
            ];
        }

        return $sales;
    }

    public function testCalculateTotalSales()
    {
        $this->assertEqualsWithDelta(self::$expectedTotal, calculateTotalSales(self::$sales), 0.01);
    }

    public function testCalculateSellerWithHighestTotalSales()
    {
        $this->assertEquals(self::$expectedTopSeller, calculateSellerWithHighestTotalSales(self::$sales));
    }

    public function testCalculateMostVolumeMonth()
    {
        $this->assertEquals(self::$expectedTopMonth, calculateMostVolumeMonth(self::$sales));
    }

    public function testCalculateAverageSales()
    {
        $this->assertEqualsWithDelta(self::$expectedAverage, calculateAverageSales(self::$sales), 0.01);
    }

    // --- Edge cases ---

    public function testCalculateTotalSalesWithEmptyArray()
    {
        $this->assertEquals(0, calculateTotalSales([]));
    }

    public function testCalculateTotalSalesWithSingleSale()
    {
        $sales = [['seller' => 'John', 'month' => 'January', 'amount' => 1500.00]];
        $this->assertEqualsWithDelta(1500.00, calculateTotalSales($sales), 0.01);
    }

    public function testCalculateTotalSalesWithNegativeAmounts()
    {
        $sales = [
            ['seller' => 'John', 'month' => 'January', 'amount' => 500.00],
            ['seller' => 'Mary', 'month' => 'January', 'amount' => -200.00],
            ['seller' => 'Peter', 'month' => 'February', 'amount' => 100.00],
        ];
        $this->assertEqualsWithDelta(400.00, calculateTotalSales($sales), 0.01);
    }

    public function testCalculateAverageSalesWithEmptyArrayThrowsDivisionByZero()
    {
        $this->expectException(\DivisionByZeroError::class);
        calculateAverageSales([]);
    }

    public function testCalculateAverageSalesWithSingleSale()
    {
        $sales = [['seller' => 'Ana', 'month' => 'March', 'amount' => 750.00]];
        $this->assertEqualsWithDelta(750.00, calculateAverageSales($sales), 0.01);
    }

    public function testCalculateSellerWithHighestTotalSalesWithEmptyArrayThrowsValueError()
    {
        $this->expectException(\ValueError::class);
        calculateSellerWithHighestTotalSales([]);
    }

    public function testCalculateMostVolumeMonthWithEmptyArrayThrowsValueError()
    {
        $this->expectException(\ValueError::class);
        calculateMostVolumeMonth([]);
    }

    public function testCalculateSellerWithHighestTotalSalesWithTie()
    {
        $sales = [
            ['seller' => 'John', 'month' => 'January', 'amount' => 200.00],
            ['seller' => 'Mary', 'month' => 'February', 'amount' => 200.00],
        ];

        $result = calculateSellerWithHighestTotalSales($sales);

        $this->assertContains($result, ['John', 'Mary']);
    }

    public function testCalculateMostVolumeMonthWithTie()
    {
        $sales = [
            ['seller' => 'John', 'month' => 'January', 'amount' => 300.00],
            ['seller' => 'Mary', 'month' => 'February', 'amount' => 300.00],
        ];

        $result = calculateMostVolumeMonth($sales);

        $this->assertContains($result, ['January', 'February']);
    }

    public function testCalculateTotalSalesBySellerWithMultipleSales()
    {
        $sales = [
            ['seller' => 'John', 'month' => 'January', 'amount' => 100.00],
            ['seller' => 'Mary', 'month' => 'January', 'amount' => 200.00],
            ['seller' => 'John', 'month' => 'February', 'amount' => 150.00],
        ];

        $expected = ['John' => 250.00, 'Mary' => 200.00];
        $this->assertEquals($expected, calculateTotalSalesBySeller($sales));
    }

    public function testCalculateTotalSalesByMonthWithMultipleSales()
    {
        $sales = [
            ['seller' => 'John', 'month' => 'January', 'amount' => 100.00],
            ['seller' => 'Mary', 'month' => 'January', 'amount' => 200.00],
            ['seller' => 'John', 'month' => 'February', 'amount' => 400.00],
        ];

        $expected = ['January' => 300.00, 'February' => 400.00];
        $this->assertEquals($expected, calculateTotalSalesByMonth($sales));
    }

    public function testCalculateAllFunctionsWithLargeDataset()
    {
        $sales = self::generateRandomSales(rand(50, 100));
        $this->assertGreaterThan(0, count($sales));

        $total = calculateTotalSales($sales);
        $average = calculateAverageSales($sales);
        $this->assertIsFloat($total);
        $this->assertIsFloat($average);

        $topSeller = calculateSellerWithHighestTotalSales($sales);
        $this->assertContains($topSeller, self::SELLERS);

        $topMonth = calculateMostVolumeMonth($sales);
        $this->assertContains($topMonth, self::MONTHS);
    }
}
