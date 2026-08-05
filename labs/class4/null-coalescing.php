<?php

$name = $_GET['name'] ?? 'Guest';
// Uses $_GET['name'] if set and not null; otherwise 'Guest'

$config = ['db_host' => 'localhost', 'db_port' => null];

$host = $config['db_host'] ?? '127.0.0.1';
echo $host; // localhost — exists and not null

$port = $config['db_port'] ?? 3306;
echo $port; // 3306 — exists but is null, uses default

$user = $config['db_user'] ?? 'root';
echo $user; // root — key doesn't exist, uses default


// PHP 7.4+: chain multiple ?? to try multiple sources
$name = $_GET['name'] ?? $_POST['name'] ?? $_COOKIE['name'] ?? 'Anonymous';

$name = 'John';
$name ??= 'Guest';
echo $name; // John — already has a value

unset($name);
$name ??= 'Guest';
echo $name; // Guest — was undefined

$config = [];
$config['host'] ??= 'localhost';
echo $config['host']; // localhost

$value = 0;

echo $value ?: 'default';   // default  (?: checks truthiness: 0 is falsy)
echo $value ?? 'default';   // 0        (?? checks isset + not-null only)
echo $value ? $value : 'default'; // default
