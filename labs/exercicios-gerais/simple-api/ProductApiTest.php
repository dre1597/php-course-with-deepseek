<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ProductApi.php';

class ProductApiTest extends TestCase
{
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/product_api_test_' . uniqid() . '.db';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    private function callProcess(string $method, mixed $body): array
    {
        ob_start();
        ProductApi::process($this->dbPath, $method, is_array($body) ? json_encode($body) : $body);
        $output = ob_get_clean();

        return [
            'code'   => http_response_code(),
            'output' => $output,
        ];
    }

    public function testMethodNotAllowedReturns405(): void
    {
        $response = $this->callProcess('GET', ['name' => 'Foo', 'price' => 10]);

        $this->assertSame(405, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertSame('Method Not Allowed', $decoded['error']);
    }

    public function testInvalidJsonReturns422(): void
    {
        $response = $this->callProcess('POST', 'not-json');

        $this->assertSame(422, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertSame('Invalid JSON', $decoded['error']);
    }

    public function testMissingNameReturns422(): void
    {
        $response = $this->callProcess('POST', ['price' => 10]);

        $this->assertSame(422, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertArrayHasKey('errors', $decoded);
        $this->assertArrayHasKey('name', $decoded['errors']);
    }

    public function testMissingPriceReturns422(): void
    {
        $response = $this->callProcess('POST', ['name' => 'Foo']);

        $this->assertSame(422, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertArrayHasKey('price', $decoded['errors']);
    }

    public function testNonNumericPriceReturns422(): void
    {
        $response = $this->callProcess('POST', ['name' => 'Foo', 'price' => 'abc']);

        $this->assertSame(422, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertArrayHasKey('price', $decoded['errors']);
    }

    public function testNegativePriceReturns422(): void
    {
        $response = $this->callProcess('POST', ['name' => 'Foo', 'price' => -5]);

        $this->assertSame(422, $response['code']);
        $decoded = json_decode($response['output'], true);
        $this->assertArrayHasKey('price', $decoded['errors']);
    }

    public function testZeroPriceIsValid(): void
    {
        $response = $this->callProcess('POST', ['name' => 'Freebie', 'price' => 0]);

        $this->assertSame(201, $response['code']);
    }

    public function testSuccessfulCreationReturns201(): void
    {
        $response = $this->callProcess('POST', [
            'name'  => 'Notebook',
            'price' => 3500.99,
        ]);

        $this->assertSame(201, $response['code']);

        $decoded = json_decode($response['output'], true);

        $this->assertArrayHasKey('id', $decoded);
        $this->assertIsInt($decoded['id']);
        $this->assertSame('Notebook', $decoded['name']);
        $this->assertSame(3500.99, $decoded['price']);
        $this->assertArrayHasKey('created_at', $decoded);
    }

    public function testRespondOutputsJson(): void
    {
        $response = $this->callProcess('POST', ['name' => 'X', 'price' => 1]);

        $decoded = json_decode($response['output'], true);
        $this->assertIsArray($decoded);
        $this->assertSame('X', $decoded['name']);
    }

    public function testResponseIsValidJson(): void
    {
        $response = $this->callProcess('POST', ['name' => 'Phone', 'price' => 999.90]);

        $decoded = json_decode($response['output'], true);

        $this->assertIsArray($decoded);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
    }

    public function testValidateReturnsEmptyForValidData(): void
    {
        $errors = ProductApi::validate(['name' => 'Foo', 'price' => '10.50']);

        $this->assertEmpty($errors);
    }

    public function testValidatePriceAcceptsStringNumber(): void
    {
        $errors = ProductApi::validate(['name' => 'Foo', 'price' => '99.99']);

        $this->assertEmpty($errors);
    }

    public function testValidateNameCannotBeOnlySpaces(): void
    {
        $errors = ProductApi::validate(['name' => '   ', 'price' => 10]);

        $this->assertArrayHasKey('name', $errors);
    }

    public function testInsertEnrichesDataWithIdPriceAndTimestamp(): void
    {
        $db = new SQLite3($this->dbPath);
        ProductApi::ensureTable($db);

        $data = ['name' => 'Mouse', 'price' => '89.90'];
        ProductApi::insert($db, $data);
        $db->close();

        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertGreaterThan(0, $data['id']);
        $this->assertSame(89.9, $data['price']);
        $this->assertIsFloat($data['price']);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertNotEmpty($data['created_at']);
    }

    public function testInsertAutoIncrementsIds(): void
    {
        $db = new SQLite3($this->dbPath);
        ProductApi::ensureTable($db);

        $a = ['name' => 'A', 'price' => 1];
        $b = ['name' => 'B', 'price' => 2];
        ProductApi::insert($db, $a);
        ProductApi::insert($db, $b);
        $db->close();

        $this->assertSame($a['id'] + 1, $b['id']);
    }

    public function testEnsureTableCreatesProductsTable(): void
    {
        $db = new SQLite3($this->dbPath);
        ProductApi::ensureTable($db);

        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");

        $this->assertNotFalse($result);
        $this->assertNotFalse($result->fetchArray());
        $db->close();
    }

    public function testEnsureTableIsIdempotent(): void
    {
        $db = new SQLite3($this->dbPath);
        ProductApi::ensureTable($db);
        ProductApi::ensureTable($db);

        $result = $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='products'");

        $this->assertSame(1, $result);
        $db->close();
    }
}
