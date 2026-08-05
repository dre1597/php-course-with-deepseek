<?php

$upper = strtoupper(...);

echo $upper('php is awesome'); // PHP IS AWESOME

// Com array_map — antes (PHP < 8.1) vs depois
$names = ['ana', 'carlos', 'bia'];

// Antes:
$uppercase = array_map('strtoupper', $names);

// Agora (PHP 8.1+):
$uppercase = array_map(strtoupper(...), $names);

print_r($uppercase); // ['ANA', 'CARLOS', 'BIA']


class Calculator
{
    public function double(int $number): int
    {
        return $number * 2;
    }

    public static function triple(int $number): int
    {
        return $number * 3;
    }
}

$calc = new Calculator();

$doubleFn = $calc->double(...);          // método de instância
$tripleFn = Calculator::triple(...); // método estático

echo $doubleFn(10);     // 20
echo $tripleFn(10);  // 30


class Logger
{
    public function info(string $msg): void
    {
        echo "[INFO] {$msg}" . PHP_EOL;
    }
}

$logger = new Logger();
$logFn = Closure::fromCallable([$logger, 'info']);
// equivalente a: $logFn = $logger->info(...);

$logFn('System started'); // [INFO] System started


$task = function (string $name): void {
    $current = Closure::getCurrent();
    // Reflection sobre a própria closure
    $ref = new ReflectionFunction($current);
    echo "Executando a closure '{$ref->getName()}' com parâmetro: {$name}" . PHP_EOL;
};

$task('import_data');
// Executing closure '{closure}' with parameter: import_data


$counter = function (int $step = 1) use (&$counter): void {
    static $value = 0;
    $value += $step;
    echo "Counter: {$value}" . PHP_EOL;

    if ($value < 10) {
        $current = Closure::getCurrent();
        $current($step);
    }
};

$counter(2);
// Counter: 2
// Counter: 4
// Counter: 6
// Counter: 8
// Counter: 10
