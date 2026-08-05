<?php

// Classes: PascalCase
class ProductController {}
class EmailService {}
class HttpClient {}

// Métodos e funções: camelCase
public function calculateTotal(): float {}
public function findById(int $id): ?object {}
function formatPhone(string $tel): string {}

// Variáveis: camelCase
$fullName = 'Maria Silva';
$stockQuantity = 42;
$isActive = true;

// Constantes: UPPER_SNAKE_CASE
const DEFAULT_LIMIT = 100;
const BASE_URL = 'https://api.example.com';
define('MAX_ATTEMPTS', 3);

// Propriedades: camelCase
private string $birthDate;
public float $unitPrice;

// Namespaces: PascalCase com vendor prefix
namespace MyApp\Services\Payment;
namespace Acme\Util\Formatting;