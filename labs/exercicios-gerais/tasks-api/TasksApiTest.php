<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TasksApi.php';

class TasksApiTest extends TestCase
{
    private const string TOKEN = 'test-token';

    private PDO $pdo;
    private TasksApi $api;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->api = new TasksApi($this->pdo, ['api_token' => self::TOKEN]);
    }

    private function authHeaders(array $extra = []): array
    {
        return array_merge(['Authorization' => 'Bearer ' . self::TOKEN], $extra);
    }

    private function request(
        string  $method,
        string  $uri,
        array   $headers = [],
        string  $body = '',
        ?string $clientIp = null
    ): array
    {
        return $this->api->dispatch($method, $uri, $headers, $body, $clientIp);
    }

    private function createTask(string $description = 'Nova tarefa', string $category = 'geral'): array
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'description' => $description,
            'category' => $category,
        ]));

        return $response['body'];
    }

    public function testRequestWithoutTokenReturns401(): void
    {
        $response = $this->request('GET', '/api/tasks');

        $this->assertSame(401, $response['status']);
    }

    public function testRequestWithWrongTokenReturns401(): void
    {
        $response = $this->request('GET', '/api/tasks', ['Authorization' => 'Bearer wrong']);

        $this->assertSame(401, $response['status']);
    }

    public function testRequestWithValidTokenIsAccepted(): void
    {
        $response = $this->request('GET', '/api/tasks', $this->authHeaders());

        $this->assertNotSame(401, $response['status']);
    }

    public function testListTasksStartsEmpty(): void
    {
        $response = $this->request('GET', '/api/tasks', $this->authHeaders());

        $this->assertSame(200, $response['status']);
        $this->assertSame([], $response['body']);
    }

    public function testCreateTaskReturns201WithTask(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'description' => 'Estudar PHP',
            'category' => 'estudo',
        ]));

        $this->assertSame(201, $response['status']);
        $this->assertArrayHasKey('id', $response['body']);
        $this->assertSame('Estudar PHP', $response['body']['description']);
        $this->assertSame('estudo', $response['body']['category']);
        $this->assertFalse($response['body']['completed']);
    }

    public function testCreateTaskDefaultsCategoryToGeral(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'description' => 'Sem categoria',
        ]));

        $this->assertSame(201, $response['status']);
        $this->assertSame('geral', $response['body']['category']);
    }

    public function testCreateTaskWithoutDescriptionReturns422(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'category' => 'geral',
        ]));

        $this->assertSame(422, $response['status']);
    }

    public function testCreateTaskWithBlankDescriptionReturns422(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'description' => '   ',
        ]));

        $this->assertSame(422, $response['status']);
    }

    public function testCreateTaskWithInvalidCategoryReturns422(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), json_encode([
            'description' => 'x',
            'category' => 'categoria-inventada',
        ]));

        $this->assertSame(422, $response['status']);
    }

    public function testCreateTaskWithInvalidJsonReturns422(): void
    {
        $response = $this->request('POST', '/api/tasks', $this->authHeaders(), '{invalid');

        $this->assertSame(422, $response['status']);
    }

    public function testListTasksReturnsCreatedTasks(): void
    {
        $this->createTask('Primeira');
        $this->createTask('Segunda');

        $response = $this->request('GET', '/api/tasks', $this->authHeaders());

        $this->assertCount(2, $response['body']);
        $this->assertSame('Primeira', $response['body'][0]['description']);
        $this->assertSame('Segunda', $response['body'][1]['description']);
    }

    public function testGetTaskReturnsTask(): void
    {
        $created = $this->createTask('Buscar essa');

        $response = $this->request('GET', '/api/tasks/' . $created['id'], $this->authHeaders());

        $this->assertSame(200, $response['status']);
        $this->assertSame('Buscar essa', $response['body']['description']);
    }

    public function testGetMissingTaskReturns404(): void
    {
        $response = $this->request('GET', '/api/tasks/999', $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testUpdateTaskChangesFields(): void
    {
        $created = $this->createTask('Original', 'pessoal');

        $response = $this->request('PUT', '/api/tasks/' . $created['id'], $this->authHeaders(), json_encode([
            'description' => 'Atualizada',
            'category' => 'trabalho',
            'completed' => true,
        ]));

        $this->assertSame(200, $response['status']);
        $this->assertSame('Atualizada', $response['body']['description']);
        $this->assertSame('trabalho', $response['body']['category']);
        $this->assertTrue($response['body']['completed']);
    }

    public function testUpdateTaskIsPartial(): void
    {
        $created = $this->createTask('Original', 'estudo');

        $response = $this->request('PUT', '/api/tasks/' . $created['id'], $this->authHeaders(), json_encode([
            'description' => 'Só descrição',
        ]));

        $this->assertSame(200, $response['status']);
        $this->assertSame('estudo', $response['body']['category']);
        $this->assertFalse($response['body']['completed']);
    }

    public function testUpdateMissingTaskReturns404(): void
    {
        $response = $this->request('PUT', '/api/tasks/999', $this->authHeaders(), json_encode([
            'description' => 'x',
        ]));

        $this->assertSame(404, $response['status']);
    }

    public function testUpdateTaskWithInvalidCategoryReturns422(): void
    {
        $created = $this->createTask('Original');

        $response = $this->request('PUT', '/api/tasks/' . $created['id'], $this->authHeaders(), json_encode([
            'category' => 'invalida',
        ]));

        $this->assertSame(422, $response['status']);
    }

    public function testUpdateTaskWithNonBooleanCompletedReturns422(): void
    {
        $created = $this->createTask('Original');

        $response = $this->request('PUT', '/api/tasks/' . $created['id'], $this->authHeaders(), json_encode([
            'completed' => 'sim',
        ]));

        $this->assertSame(422, $response['status']);
    }

    public function testToggleTaskCompletesPendingTask(): void
    {
        $created = $this->createTask('Toggle me');

        $response = $this->request('PATCH', '/api/tasks/' . $created['id'] . '/toggle', $this->authHeaders());

        $this->assertSame(200, $response['status']);
        $this->assertTrue($response['body']['completed']);
    }

    public function testToggleTaskReopensCompletedTask(): void
    {
        $created = $this->createTask('Toggle me');

        $this->request('PATCH', '/api/tasks/' . $created['id'] . '/toggle', $this->authHeaders());
        $response = $this->request('PATCH', '/api/tasks/' . $created['id'] . '/toggle', $this->authHeaders());

        $this->assertFalse($response['body']['completed']);
    }

    public function testToggleMissingTaskReturns404(): void
    {
        $response = $this->request('PATCH', '/api/tasks/999/toggle', $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testDeleteTaskReturns204(): void
    {
        $created = $this->createTask('Apagar');

        $response = $this->request('DELETE', '/api/tasks/' . $created['id'], $this->authHeaders());

        $this->assertSame(204, $response['status']);
        $this->assertNull($response['body']);
    }

    public function testDeleteTaskActuallyRemovesIt(): void
    {
        $created = $this->createTask('Apagar');

        $this->request('DELETE', '/api/tasks/' . $created['id'], $this->authHeaders());
        $response = $this->request('GET', '/api/tasks/' . $created['id'], $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testDeleteMissingTaskReturns404(): void
    {
        $response = $this->request('DELETE', '/api/tasks/999', $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testDeleteOnCollectionReturns405(): void
    {
        $response = $this->request('DELETE', '/api/tasks', $this->authHeaders());

        $this->assertSame(405, $response['status']);
    }

    public function testToggleWithGetReturns405(): void
    {
        $created = $this->createTask('x');

        $response = $this->request('GET', '/api/tasks/' . $created['id'] . '/toggle', $this->authHeaders());

        $this->assertSame(405, $response['status']);
    }

    public function testUnknownRouteReturns404(): void
    {
        $response = $this->request('GET', '/api/outra-coisa', $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testNonNumericIdReturns404(): void
    {
        $response = $this->request('GET', '/api/tasks/abc', $this->authHeaders());

        $this->assertSame(404, $response['status']);
    }

    public function testGetReturnsEtagHeader(): void
    {
        $this->createTask('Com etag');

        $response = $this->request('GET', '/api/tasks', $this->authHeaders());

        $this->assertArrayHasKey('ETag', $response['headers']);
    }

    public function testGetWithMatchingEtagReturns304(): void
    {
        $this->createTask('Com etag');

        $first = $this->request('GET', '/api/tasks', $this->authHeaders());
        $etag = $first['headers']['ETag'];

        $second = $this->request('GET', '/api/tasks', $this->authHeaders(['If-None-Match' => $etag]));

        $this->assertSame(304, $second['status']);
        $this->assertNull($second['body']);
    }

    public function testGetWithDifferentEtagReturns200(): void
    {
        $this->createTask('Com etag');

        $response = $this->request('GET', '/api/tasks', $this->authHeaders([
            'If-None-Match' => '"etag-inventado"',
        ]));

        $this->assertSame(200, $response['status']);
    }

    public function testGetSingleTaskHasEtag(): void
    {
        $created = $this->createTask('Só uma');

        $response = $this->request('GET', '/api/tasks/' . $created['id'], $this->authHeaders());

        $this->assertArrayHasKey('ETag', $response['headers']);
    }

    public function testRateLimitBlocksExcessRequests(): void
    {
        $api = new TasksApi($this->pdo, ['api_token' => self::TOKEN, 'rate_limit' => 2]);

        $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.1');
        $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.1');

        $response = $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.1');

        $this->assertSame(429, $response['status']);
    }

    public function testRateLimitIsPerClientIp(): void
    {
        $api = new TasksApi($this->pdo, ['api_token' => self::TOKEN, 'rate_limit' => 1]);

        $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.1');

        $other = $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.2');

        $this->assertNotSame(429, $other['status']);
    }

    public function testRateLimitRunsBeforeAuthentication(): void
    {
        $api = new TasksApi($this->pdo, ['api_token' => self::TOKEN, 'rate_limit' => 1]);

        $api->dispatch('GET', '/api/tasks', $this->authHeaders(), '', '10.0.0.1');

        $response = $api->dispatch('GET', '/api/tasks', [], '', '10.0.0.1');

        $this->assertSame(429, $response['status']);
    }

    public function testResponseHasJsonContentType(): void
    {
        $response = $this->request('GET', '/api/tasks', $this->authHeaders());

        $this->assertSame('application/json', $response['headers']['Content-Type']);
    }
}
