<?php

// === 16 — Magic Methods ===

// __get($name) — Access to Inaccessible Property
class Container
{
    private array $dataPayload = [];

    public function __get(string $name): mixed
    {
        return $this->dataPayload[$name] ?? null;
    }
}

$container = new Container();
echo $container->any_key; // null (no error!)

// __set($name, $value) — Write to Inaccessible Property
class DynamicConfig
{
    private array $values = [];

    public function __set(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function getAll(): array
    {
        return $this->values;
    }
}

$config = new DynamicConfig();
$config->debug = true;
$config->host  = 'localhost';
$config->port = 3306;

print_r($config->getAll());
/*
Array
(
    [debug] => 1
    [host] => localhost
    [port] => 3306
)
*/

// __call($method, $args) — Call to Inaccessible Method
class MicroOrm
{
    public function __call(string $method, array $args): mixed
    {
        if (str_starts_with($method, 'findBy')) {
            $column = lcfirst(substr($method, 6));
            return $this->findBy($column, $args[0]);
        }

        throw new BadMethodCallException("Method {$method} does not exist");
    }

    private function findBy(string $column, mixed $value): ?array
    {
        // simulated query
        echo "SELECT * FROM table WHERE {$column} = {$value}\n";
        return null;
    }
}

$orm = new MicroOrm();
$orm->findByName('Mary');   // SELECT * FROM table WHERE name = Mary
$orm->findByEmail('a@b.com'); // SELECT * FROM table WHERE email = a@b.com

// __toString() — String Representation
class Money
{
    public function __construct(
        private float $value,
        private string $currency = 'USD',
    ) {}

    public function __toString(): string
    {
        return '$ ' . number_format($this->value, 2, '.', ',');
    }
}

$price = new Money(199.9);
echo $price;              // $ 199.90
echo "Price: {$price}";  // Price: $ 199.90

// __invoke() — Object as Function
class EmailValidator
{
    public function __invoke(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

$validator = new EmailValidator();

var_dump($validator('user@domain.com')); // bool(true)
var_dump($validator('invalid'));         // bool(false)

// Usage as callback:
$emails = ['a@b.com', 'invalid', 'c@d.com'];
$valid = array_filter($emails, $validator);
print_r($valid); // ['a@b.com', 'c@d.com']

// __debugInfo() — Control var_dump() Output
class UserDebug
{
    public function __construct(
        private string $name,
        private string $password,
        private string $email,
    ) {}

    public function __debugInfo(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => '***REDACTED***',
        ];
    }
}

$u = new UserDebug('Mary', 'pass123', 'mary@email.com');
var_dump($u);
/*
object(UserDebug)#1 (3) {
  ["name"]=> string(4) "Mary"
  ["email"]=> string(14) "mary@email.com"
  ["password"]=> string(13) "***REDACTED***"
}
*/
