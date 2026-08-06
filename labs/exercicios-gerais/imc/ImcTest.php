<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/imc.php';

class ImcTest extends TestCase
{
    public function testImcCalculation(): void
    {
        $this->assertEqualsWithDelta(22.857, getUserIMC(70, 1.75), 0.001);
    }

    public function testImcUnderweight(): void
    {
        $imc = getUserIMC(45, 1.75);
        $this->assertLessThan(18.5, $imc);
        $this->assertEqualsWithDelta(14.694, $imc, 0.001);
    }

    public function testImcNormalWeightLowerBoundary(): void
    {
        $imc = getUserIMC(74, 2.0);
        $this->assertEqualsWithDelta(18.5, $imc, 0.001);
        $this->assertGreaterThanOrEqual(18.5, $imc);
    }

    public function testImcJustAboveUnderweight(): void
    {
        $imc = getUserIMC(46.25, 1.581);
        $this->assertGreaterThan(18.5, $imc);
    }

    public function testImcNormalWeightUpperBoundary(): void
    {
        $imc = getUserIMC(99.9, 2.0);
        $this->assertLessThan(25, $imc);
        $this->assertEqualsWithDelta(24.975, $imc, 0.001);
    }

    public function testImcOverweightLowerBoundary(): void
    {
        $imc = getUserIMC(100, 2.0);
        $this->assertEqualsWithDelta(25.0, $imc, 0.001);
        $this->assertGreaterThanOrEqual(25, $imc);
    }

    public function testImcJustBelowObesity(): void
    {
        $imc = getUserIMC(119.9, 2.0);
        $this->assertLessThan(30, $imc);
        $this->assertEqualsWithDelta(29.975, $imc, 0.001);
    }

    public function testImcObesityBoundary(): void
    {
        $imc = getUserIMC(120, 2.0);
        $this->assertEqualsWithDelta(30.0, $imc, 0.001);
        $this->assertGreaterThanOrEqual(30, $imc);
    }

    public function testImcSevereObesity(): void
    {
        $imc = getUserIMC(140, 1.6);
        $this->assertGreaterThan(40, $imc);
        $this->assertEqualsWithDelta(54.688, $imc, 0.001);
    }

    public function testImcWithZeroWeight(): void
    {
        $imc = getUserIMC(0, 1.75);
        $this->assertEqualsWithDelta(0.0, $imc, 0.001);
        $this->assertLessThan(18.5, $imc);
    }

    public function testImcWithNegativeWeight(): void
    {
        $imc = getUserIMC(-70, 1.75);
        $this->assertLessThan(0, $imc);
        $this->assertEqualsWithDelta(-22.857, $imc, 0.001);
    }

    public function testImcWithNegativeHeight(): void
    {
        $imc = getUserIMC(70, -1.75);
        $this->assertEqualsWithDelta(22.857, $imc, 0.001);
    }

    public function testImcWithBothNegative(): void
    {
        $imc = getUserIMC(-70, -1.75);
        $this->assertEqualsWithDelta(-22.857, $imc, 0.001);
    }

    public function testImcWithZeroHeight(): void
    {
        $this->expectException(DivisionByZeroError::class);
        getUserIMC(70, 0);
    }

    public function testImcWithNegativeZeroHeight(): void
    {
        $this->expectException(DivisionByZeroError::class);
        getUserIMC(70, -0);
    }

    public function testImcWithVeryLargeWeight(): void
    {
        $imc = getUserIMC(999999, 1.75);
        $this->assertGreaterThan(100000, $imc);
    }

    public function testImcWithVerySmallHeight(): void
    {
        $imc = getUserIMC(70, 0.01);
        $this->assertGreaterThan(100000, $imc);
    }

    public function testImcWithVeryLargeHeight(): void
    {
        $imc = getUserIMC(70, 100);
        $this->assertLessThan(0.01, $imc);
    }

    public function testImcWithIntegerInputs(): void
    {
        $imc = getUserIMC(80, 2);
        $this->assertEqualsWithDelta(20.0, $imc, 0.001);
    }

    public function testImcWithFloatWeightIntegerHeight(): void
    {
        $imc = getUserIMC(80.5, 2);
        $this->assertEqualsWithDelta(20.125, $imc, 0.001);
    }

    public function testImcWithIntegerWeightFloatHeight(): void
    {
        $imc = getUserIMC(80, 1.8);
        $this->assertEqualsWithDelta(24.691, $imc, 0.001);
    }
}
