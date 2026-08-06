<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/multiplication-table.php';

class MultiplicationTableTest extends TestCase
{
    private function captureTable(int $n): string
    {
        ob_start();
        getMultiplicationTable($n);
        return ob_get_clean();
    }

    private function parseTable(string $output): array
    {
        $lines = explode("\n", trim($output));
        $results = [];
        foreach ($lines as $line) {
            if (preg_match('/^(-?\d+)\s*x\s*(\d+)\s*=\s*(-?\d+)$/', $line, $matches)) {
                $results[] = [
                    'base' => (int) $matches[1],
                    'multiplier' => (int) $matches[2],
                    'result' => (int) $matches[3],
                ];
            }
        }
        return $results;
    }

    public function testMultiplicationTableForPositiveNumber(): void
    {
        $output = $this->captureTable(7);
        $rows = $this->parseTable($output);

        $this->assertCount(10, $rows);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertEquals(7, $rows[$i - 1]['base']);
            $this->assertEquals($i, $rows[$i - 1]['multiplier']);
            $this->assertEquals(7 * $i, $rows[$i - 1]['result']);
        }
    }

    public function testMultiplicationTableForZero(): void
    {
        $output = $this->captureTable(0);
        $rows = $this->parseTable($output);

        $this->assertCount(10, $rows);
        foreach ($rows as $i => $row) {
            $this->assertEquals(0, $row['base']);
            $this->assertEquals($i + 1, $row['multiplier']);
            $this->assertEquals(0, $row['result']);
        }
    }

    public function testMultiplicationTableForOne(): void
    {
        $output = $this->captureTable(1);
        $rows = $this->parseTable($output);

        $this->assertCount(10, $rows);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertEquals(1, $rows[$i - 1]['base']);
            $this->assertEquals($i, $rows[$i - 1]['result']);
        }
    }

    public function testMultiplicationTableForNegativeNumber(): void
    {
        $output = $this->captureTable(-3);
        $rows = $this->parseTable($output);

        $this->assertCount(10, $rows);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertEquals(-3, $rows[$i - 1]['base']);
            $this->assertEquals($i, $rows[$i - 1]['multiplier']);
            $this->assertEquals(-3 * $i, $rows[$i - 1]['result']);
        }
    }

    public function testMultiplicationTableForLargeNumber(): void
    {
        $output = $this->captureTable(9999);
        $rows = $this->parseTable($output);

        $this->assertCount(10, $rows);
        $this->assertEquals(9999, $rows[0]['base']);
        $this->assertEquals(99990, $rows[9]['result']);
    }

    public function testMultiplicationTableExactFormat(): void
    {
        $output = $this->captureTable(5);

        $expectedLines = [
            "5 x 1 = 5",
            "5 x 2 = 10",
            "5 x 3 = 15",
            "5 x 4 = 20",
            "5 x 5 = 25",
            "5 x 6 = 30",
            "5 x 7 = 35",
            "5 x 8 = 40",
            "5 x 9 = 45",
            "5 x 10 = 50",
        ];

        $lines = explode("\n", trim($output));
        $this->assertCount(10, $lines);
        foreach ($expectedLines as $i => $expected) {
            $this->assertSame($expected, $lines[$i]);
        }
    }

    public function testMultiplicationTableLinesEndWithNewline(): void
    {
        $output = $this->captureTable(42);
        $lines = explode("\n", $output);

        $this->assertCount(11, $lines);
        $this->assertSame('', $lines[10]);
    }

    public function testMultiplicationTableNotEmpty(): void
    {
        $output = $this->captureTable(13);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('13 x 1 = 13', $output);
        $this->assertStringContainsString('13 x 10 = 130', $output);
    }
}
