<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/csv-processor.php';

class CsvProcessorTest extends TestCase
{
    private string $sourceFile;
    private string $destinationFile;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/csv_test_' . uniqid() . '/';
        mkdir($this->tempDir, 0777, true);
        $this->sourceFile = $this->tempDir . 'data.csv';
        $this->destinationFile = $this->tempDir . 'filtered.csv';
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . '*'));
        rmdir($this->tempDir);
    }

    private function createCsv(array $rows): void
    {
        $handle = fopen($this->sourceFile, 'w');
        foreach ($rows as $row) {
            fputcsv($handle, $row, escape: '');
        }
        fclose($handle);
    }

    private function readCsv(): array
    {
        $rows = [];
        $handle = fopen($this->destinationFile, 'r');
        while (($row = fgetcsv($handle, escape: '')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    public function testFiltersRowsByNumericColumn(): void
    {
        $this->createCsv([
            ['name', 'age', 'city'],
            ['Alice', '30', 'NYC'],
            ['Bob', '20', 'LA'],
            ['Charlie', '35', 'Chicago'],
            ['Diana', '25', 'Miami'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['age'] > 25;
        });

        $this->assertSame(2, $count);

        $result = $this->readCsv();
        $this->assertCount(3, $result); // header + 2 rows
        $this->assertSame(['name', 'age', 'city'], $result[0]);
        $this->assertSame(['Alice', '30', 'NYC'], $result[1]);
        $this->assertSame(['Charlie', '35', 'Chicago'], $result[2]);
    }

    public function testFiltersRowsByStringColumn(): void
    {
        $this->createCsv([
            ['product', 'category', 'price'],
            ['Laptop', 'Electronics', '3500'],
            ['Shirt', 'Clothing', '89'],
            ['Mouse', 'Electronics', '120'],
            ['Jacket', 'Clothing', '250'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return $row['category'] === 'Electronics';
        });

        $this->assertSame(2, $count);

        $result = $this->readCsv();
        $this->assertCount(3, $result);
        $this->assertSame(['Laptop', 'Electronics', '3500'], $result[1]);
        $this->assertSame(['Mouse', 'Electronics', '120'], $result[2]);
    }

    public function testNoRowsMatchReturnsOnlyHeader(): void
    {
        $this->createCsv([
            ['name', 'score'],
            ['Alice', '70'],
            ['Bob', '65'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['score'] >= 90;
        });

        $this->assertSame(0, $count);

        $result = $this->readCsv();
        $this->assertCount(1, $result);
        $this->assertSame(['name', 'score'], $result[0]);
    }

    public function testAllRowsMatchReturnsFullFile(): void
    {
        $this->createCsv([
            ['id', 'status'],
            ['1', 'active'],
            ['2', 'active'],
            ['3', 'active'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return $row['status'] === 'active';
        });

        $this->assertSame(3, $count);

        $result = $this->readCsv();
        $this->assertCount(4, $result); // header + 3 rows
    }

    public function testEmptyInputFileReturnsZero(): void
    {
        file_put_contents($this->sourceFile, '');

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return true;
        });

        $this->assertSame(0, $count);
        $this->assertSame('', file_get_contents($this->destinationFile));
    }

    public function testInputFileWithOnlyHeadersReturnsZero(): void
    {
        $this->createCsv([
            ['col1', 'col2', 'col3'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return true;
        });

        $this->assertSame(0, $count);
    }

    public function testOutputIsValidCsv(): void
    {
        $this->createCsv([
            ['city', 'population'],
            ['Tokyo', '37000000'],
            ['Delhi', '32000000'],
            ['Oslo', '1000000'],
        ]);

        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['population'] > 5000000;
        });

        $result = $this->readCsv();
        $this->assertCount(3, $result);
        $this->assertCount(2, $result[0]); // header has 2 cols
        $this->assertCount(2, $result[1]); // data row has 2 cols
        $this->assertCount(2, $result[2]);
    }

    public function testRowsWithSpecialCharactersArePreserved(): void
    {
        $this->createCsv([
            ['name', 'bio'],
            ['Alice', 'Loves PHP, "coding" & testing'],
            ['Bob', 'Enjoys hikes, yoga...'],
        ]);

        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return $row['name'] === 'Bob';
        });

        $result = $this->readCsv();
        $this->assertCount(2, $result);
        $this->assertSame('Bob', $result[1][0]);
        $this->assertSame('Enjoys hikes, yoga...', $result[1][1]);
    }

    public function testIntColumnValuesAreStringsInRow(): void
    {
        $this->createCsv([
            ['id', 'value'],
            ['1', '100'],
            ['2', '200'],
        ]);

        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['id'] > 1;
        });

        $result = $this->readCsv();
        $this->assertSame('2', $result[1][0]);
        $this->assertSame('200', $result[1][1]);
    }

    public function testSourceFileDoesNotExist(): void
    {
        $count = @filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return true;
        });

        $this->assertSame(0, $count);
        $this->assertFileDoesNotExist($this->destinationFile);
    }

    public function testMalformedRowWithExtraColumnsThrowsValueError(): void
    {
        $this->createCsv([
            ['name', 'age'],
            ['Alice', '30'],
            ['Bob', '20', 'extra_column'],
        ]);

        $this->expectException(ValueError::class);

        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['age'] > 25;
        });
    }

    public function testMalformedRowWithMissingColumnsThrowsValueError(): void
    {
        $this->createCsv([
            ['name', 'age', 'city'],
            ['Alice', '30', 'NYC'],
            ['Bob'],
        ]);

        $this->expectException(ValueError::class);

        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return (int)$row['age'] > 25;
        });
    }

    public function testRowsWithFalsyStringValues(): void
    {
        $this->createCsv([
            ['user', 'active', 'note'],
            ['alice', '1', ''],
            ['bob', '0', 'vacation'],
            ['carol', '0', ''],
            ['dave', '1', 'available'],
        ]);

        $count = filterCsv($this->sourceFile, $this->destinationFile, function (array $row) {
            return $row['active'] === '0' && $row['note'] === '';
        });

        $this->assertSame(1, $count);

        $result = $this->readCsv();
        $this->assertCount(2, $result);
        $this->assertSame(['carol', '0', ''], $result[1]);
    }

    public function testCallbackReceivesAssociativeArrayWithHeaderKeys(): void
    {
        $this->createCsv([
            ['product', 'price', 'stock'],
            ['Widget', '19.99', '42'],
        ]);

        $keysInCallback = [];
        filterCsv($this->sourceFile, $this->destinationFile, function (array $row) use (&$keysInCallback) {
            $keysInCallback = array_keys($row);

            return true;
        });

        $this->assertSame(['product', 'price', 'stock'], $keysInCallback);
    }
}
