<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/sales-analysis.php';


class SalesAnalysisTest extends TestCase
{
    private static array $sales = [
        ['seller' => 'John', 'month' => 'January', 'amount' => 1500.00],
        ['seller' => 'Mary', 'month' => 'January', 'amount' => 2300.50],
        ['seller' => 'John', 'month' => 'February', 'amount' => 1800.00],
        ['seller' => 'Peter', 'month' => 'January', 'amount' => 1200.75],
        ['seller' => 'Mary', 'month' => 'February', 'amount' => 3100.00],
    ];

    public function testCalculateTotalSales()
    {
        $this->assertEqualsWithDelta(9901.25, calculateTotalSales(SalesAnalysisTest::$sales), 0.01);
    }

    public function testCalculateSellerWithHighestTotalSales()
    {
        $this->assertEquals('Mary', calculateSellerWithHighestTotalSales(SalesAnalysisTest::$sales));
    }

    public function testCalculateMostVolumeMonth()
    {
        $this->assertEquals('January', calculateMostVolumeMonth(SalesAnalysisTest::$sales));
    }

    public function testCalculateAverageSales()
    {
        $this->assertEqualsWithDelta(1980.25, calculateAverageSales(SalesAnalysisTest::$sales), 0.01);
    }
}
