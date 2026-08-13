<?php

class TasksApi
{
    private const int DEFAULT_RATE_LIMIT = 60;
    private const int RATE_LIMIT_WINDOW = 60;
    private const array VALID_CATEGORIES = ['geral', 'trabalho', 'pessoal', 'estudo'];

    private string $apiToken;
    private int $rateLimit;

    public function __construct(private readonly PDO $pdo, array $options = [])
    {
        $this->apiToken = $options['api_token'] ?? 'secret-token';
        $this->rateLimit = (int)($options['rate_limit'] ?? self::DEFAULT_RATE_LIMIT);

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS tasks (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                description TEXT    NOT NULL,
                category    TEXT    NOT NULL DEFAULT \'geral\',
                completed   INTEGER NOT NULL DEFAULT 0,
                created_at  TEXT    NOT NULL DEFAULT (datetime(\'now\'))
            )
        ');

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS rate_limits (
                identifier    TEXT PRIMARY KEY,
                window_start  INTEGER NOT NULL,
                request_count INTEGER NOT NULL
            )
        ');
    }

    public function dispatch(
        string $method,
        string $uri,
        array $headers = [],
        string $body = '',
        ?string $clientIp = null
    ): array {
        $headers = $this->normalizeHeaders($headers);

        if ($this->isRateLimited($clientIp ?? '127.0.0.1')) {
            return $this->respond(429, ['error' => 'Too Many Requests']);
        }

        if (!$this->isAuthenticated($headers)) {
            return $this->respond(401, ['error' => 'Unauthorized']);
        }

        return $this->route($method, $uri, $headers, $body);
    }

    public function run(): void
    {
        $response = $this->dispatch(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $this->extractRequestHeaders(),
            file_get_contents('php://input') ?: '',
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        http_response_code($response['status']);

        foreach ($response['headers'] as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($response['body'] !== null) {
            echo json_encode($response['body'], JSON_UNESCAPED_UNICODE);
        }
    }

    private function route(string $method, string $uri, array $headers, string $body): array
    {
        $segments = $this->parseSegments($uri);

        if (($segments[0] ?? '') !== 'api' || ($segments[1] ?? '') !== 'tasks') {
            return $this->respond(404, ['error' => 'Not Found']);
        }

        $id = $this->extractId($segments);

        if ($id === false) {
            return $this->respond(404, ['error' => 'Not Found']);
        }

        $action = $segments[3] ?? null;

        if ($action !== null && ($action !== 'toggle' || isset($segments[4]))) {
            return $this->respond(404, ['error' => 'Not Found']);
        }

        if ($id === null) {
            return $this->routeCollection($method, $headers, $body);
        }

        if ($action === 'toggle') {
            return $method === 'PATCH'
                ? $this->toggleTask($id)
                : $this->respond(405, ['error' => 'Method Not Allowed']);
        }

        return $this->routeItem($method, $id, $headers, $body);
    }

    private function routeCollection(string $method, array $headers, string $body): array
    {
        return match ($method) {
            'GET' => $this->listTasks($headers),
            'POST' => $this->createTask($body),
            default => $this->respond(405, ['error' => 'Method Not Allowed']),
        };
    }

    private function routeItem(string $method, int $id, array $headers, string $body): array
    {
        return match ($method) {
            'GET' => $this->getTask($id, $headers),
            'PUT' => $this->updateTask($id, $body),
            'DELETE' => $this->deleteTask($id),
            default => $this->respond(405, ['error' => 'Method Not Allowed']),
        };
    }

    private function listTasks(array $headers): array
    {
        $tasks = $this->pdo->query('SELECT * FROM tasks ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);

        $data = array_map($this->serializeTask(...), $tasks);

        return $this->respondWithEtag($data, $headers);
    }

    private function getTask(int $id, array $headers): array
    {
        $task = $this->findTask($id);

        if ($task === null) {
            return $this->respond(404, ['error' => 'Task not found']);
        }

        return $this->respondWithEtag($this->serializeTask($task), $headers);
    }

    private function createTask(string $body): array
    {
        $input = $this->decodeJson($body);

        if ($input === null) {
            return $this->respond(422, ['error' => 'Invalid JSON']);
        }

        $description = trim($input['description'] ?? '');

        if ($description === '') {
            return $this->respond(422, ['error' => 'description is required']);
        }

        $category = $input['category'] ?? 'geral';

        if (!$this->isValidCategory($category)) {
            return $this->respond(422, ['error' => 'Invalid category']);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (description, category) VALUES (:description, :category)'
        );
        $stmt->execute(['description' => $description, 'category' => $category]);

        $task = $this->findTask((int)$this->pdo->lastInsertId());

        return $this->respond(201, $this->serializeTask($task));
    }

    private function updateTask(int $id, string $body): array
    {
        $current = $this->findTask($id);

        if ($current === null) {
            return $this->respond(404, ['error' => 'Task not found']);
        }

        $input = $this->decodeJson($body);

        if ($input === null) {
            return $this->respond(422, ['error' => 'Invalid JSON']);
        }

        $description = $input['description'] ?? $current['description'];

        if (trim($description) === '') {
            return $this->respond(422, ['error' => 'description cannot be empty']);
        }

        $category = $input['category'] ?? $current['category'];

        if (!$this->isValidCategory($category)) {
            return $this->respond(422, ['error' => 'Invalid category']);
        }

        if (array_key_exists('completed', $input) && !is_bool($input['completed'])) {
            return $this->respond(422, ['error' => 'completed must be a boolean']);
        }

        $completed = $input['completed'] ?? (bool)(int)$current['completed'];

        $stmt = $this->pdo->prepare(
            'UPDATE tasks
             SET description = :description, category = :category, completed = :completed
             WHERE id = :id'
        );
        $stmt->execute([
            'description' => trim($description),
            'category' => $category,
            'completed' => $completed ? 1 : 0,
            'id' => $id,
        ]);

        return $this->respond(200, $this->serializeTask($this->findTask($id)));
    }

    private function toggleTask(int $id): array
    {
        $current = $this->findTask($id);

        if ($current === null) {
            return $this->respond(404, ['error' => 'Task not found']);
        }

        $newCompleted = (bool)(int)$current['completed'] ? 0 : 1;

        $stmt = $this->pdo->prepare('UPDATE tasks SET completed = :completed WHERE id = :id');
        $stmt->execute(['completed' => $newCompleted, 'id' => $id]);

        return $this->respond(200, $this->serializeTask($this->findTask($id)));
    }

    private function deleteTask(int $id): array
    {
        if ($this->findTask($id) === null) {
            return $this->respond(404, ['error' => 'Task not found']);
        }

        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $this->respond(204, null);
    }

    private function findTask(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        return $task === false ? null : $task;
    }

    private function serializeTask(array $task): array
    {
        return [
            'id' => (int)$task['id'],
            'description' => $task['description'],
            'category' => $task['category'],
            'completed' => (bool)(int)$task['completed'],
            'created_at' => $task['created_at'],
        ];
    }

    private function decodeJson(string $body): ?array
    {
        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }

    private function isValidCategory(string $category): bool
    {
        return in_array($category, self::VALID_CATEGORIES, true);
    }

    private function isAuthenticated(array $headers): bool
    {
        return ($headers['authorization'] ?? '') === 'Bearer ' . $this->apiToken;
    }

    private function isRateLimited(string $identifier): bool
    {
        $now = time();

        $stmt = $this->pdo->prepare(
            'SELECT window_start, request_count FROM rate_limits WHERE identifier = :id'
        );
        $stmt->execute(['id' => $identifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false && ($now - (int)$row['window_start']) < self::RATE_LIMIT_WINDOW) {
            $windowStart = (int)$row['window_start'];
            $count = (int)$row['request_count'] + 1;
        } else {
            $windowStart = $now;
            $count = 1;
        }

        $this->pdo->prepare(
            'INSERT INTO rate_limits (identifier, window_start, request_count)
             VALUES (:id, :ws, :count)
             ON CONFLICT(identifier) DO UPDATE SET
                 window_start = excluded.window_start,
                 request_count = excluded.request_count'
        )->execute(['id' => $identifier, 'ws' => $windowStart, 'count' => $count]);

        return $count > $this->rateLimit;
    }

    private function respondWithEtag(array $data, array $headers): array
    {
        $etag = '"' . md5(json_encode($data, JSON_UNESCAPED_UNICODE)) . '"';

        if (($headers['if-none-match'] ?? '') === $etag) {
            return $this->respond(304, null, ['ETag' => $etag]);
        }

        return $this->respond(200, $data, ['ETag' => $etag]);
    }

    private function respond(int $status, ?array $body, array $headers = []): array
    {
        return [
            'status' => $status,
            'headers' => array_merge(['Content-Type' => 'application/json'], $headers),
            'body' => $body,
        ];
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }

        return $normalized;
    }

    private function parseSegments(string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        return array_values(array_filter(explode('/', trim($path, '/'))));
    }

    private function extractId(array $segments): int|null|false
    {
        if (!isset($segments[2])) {
            return null;
        }

        return ctype_digit($segments[2]) ? (int)$segments[2] : false;
    }

    private function extractRequestHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            if ($headers !== false) {
                return $headers;
            }
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
