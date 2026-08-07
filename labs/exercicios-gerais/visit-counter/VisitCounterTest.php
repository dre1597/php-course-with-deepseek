<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/visit-counter.php';

class VisitCounterTest extends TestCase
{
    private string $testFile;

    protected function setUp(): void
    {
        $this->testFile = sys_get_temp_dir() . '/counter_test_' . uniqid() . '.txt';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testCounterStartsAtOneWhenFileDoesNotExist(): void
    {
        $count = incrementVisitCounter($this->testFile);

        $this->assertSame(1, $count);
    }

    public function testFirstVisitCreatesFileWithValueOne(): void
    {
        incrementVisitCounter($this->testFile);

        $content = file_get_contents($this->testFile);

        $this->assertSame('1', $content);
    }

    public function testSecondVisitIncrementsToTwo(): void
    {
        incrementVisitCounter($this->testFile);
        $count = incrementVisitCounter($this->testFile);

        $this->assertSame(2, $count);
    }

    public function testFileContainsCorrectValueAfterMultipleVisits(): void
    {
        incrementVisitCounter($this->testFile);
        incrementVisitCounter($this->testFile);
        incrementVisitCounter($this->testFile);

        $content = file_get_contents($this->testFile);

        $this->assertSame('3', $content);
    }

    public function testCounterIncrementsConsistentlyOverManyVisits(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $count = incrementVisitCounter($this->testFile);
            $this->assertSame($i, $count);
        }
    }

    public function testCounterReadsExistingValueFromFile(): void
    {
        file_put_contents($this->testFile, '42', LOCK_EX);

        $count = incrementVisitCounter($this->testFile);

        $this->assertSame(43, $count);
    }

    public function testCounterHandlesFileWithWhitespace(): void
    {
        file_put_contents($this->testFile, "  7\n", LOCK_EX);

        $count = incrementVisitCounter($this->testFile);

        $this->assertSame(8, $count);
    }

    public function testCounterReturnsIntegerNotString(): void
    {
        $count = incrementVisitCounter($this->testFile);

        $this->assertIsInt($count);
    }

    public function testMultipleCallsToSameFilePersistCorrectly(): void
    {
        $count1 = incrementVisitCounter($this->testFile);
        $count2 = incrementVisitCounter($this->testFile);
        $count3 = incrementVisitCounter($this->testFile);

        $this->assertSame(1, $count1);
        $this->assertSame(2, $count2);
        $this->assertSame(3, $count3);
    }

    public function testCounterIsolatedBetweenDifferentFiles(): void
    {
        $fileA = sys_get_temp_dir() . '/counter_a_' . uniqid() . '.txt';
        $fileB = sys_get_temp_dir() . '/counter_b_' . uniqid() . '.txt';

        try {
            incrementVisitCounter($fileA);
            incrementVisitCounter($fileA);

            incrementVisitCounter($fileB);

            $this->assertSame('2', file_get_contents($fileA));
            $this->assertSame('1', file_get_contents($fileB));
        } finally {
            @unlink($fileA);
            @unlink($fileB);
        }
    }
}
