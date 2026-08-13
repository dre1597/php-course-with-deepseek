<?php

require_once __DIR__ . '/TasksApi.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/data/tasks.db', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$api = new TasksApi($pdo, [
    'api_token' => getenv('TASKS_API_TOKEN') ?: 'secret-token',
]);

$api->run();
