<?php

function listCategories(array $categories, int $level = 0): void
{
    foreach ($categories as $cat) {
        echo str_repeat('  ', $level) . "- {$cat['name']}" . PHP_EOL;
        if (!empty($cat['children'])) {
            listCategories($cat['children'], $level + 1);
        }
    }
}

$tree = [
    [
        'name'   => 'Electronics',
        'children' => [
            ['name' => 'Smartphones', 'children' => []],
            ['name' => 'Laptops', 'children' => []],
        ],
    ],
    [
        'name'   => 'Books',
        'children' => [
            ['name' => 'Fiction',    'children' => []],
            ['name' => 'Technical',  'children' => []],
            ['name' => 'Biography', 'children' => []],
        ],
    ],
];

listCategories($tree);
/*
- Electronics
  - Smartphones
  - Laptops
- Books
  - Fiction
  - Technical
  - Biography
*/


#[\NoDiscard]
function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

// Chamada correta:
$token = generateToken();

// Incorrect usage — return value is discarded, triggers a notice:
// generateToken();
// Notice: the return value of function generateToken() should not be discarded

// Também funciona em métodos:
class Database
{
    #[\NoDiscard]
    public function connect(): self
    {
        // connection logic
        return $this;
    }
}

$db = new Database();
$db->connect(); // Notice: return value discarded

// Correto:
$db = (new Database())->connect();
