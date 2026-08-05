<?php

$active  = true;
$blocked = false;

var_dump($active && $blocked);  // false
var_dump($active and $blocked); // false (lower precedence than &&)

var_dump($active || $blocked);  // true
var_dump($active or $blocked);  // true (lower precedence than ||)

var_dump(!$active);             // false
var_dump(!$blocked);            // true

var_dump($active xor $blocked);  // true — one true, one false
var_dump($active xor true);     // false — both true
var_dump(false xor false);      // false — both false


// && binds before =
$result = true && false;
var_dump($result); // bool(false) — parsed as $result = (true && false)

// and binds AFTER =
$result = true and false;
var_dump($result); // bool(true)! — parsed as ($result = true) and false

$result = false || true;
var_dump($result); // bool(true) — $result = (false || true)

$result = false or true;
var_dump($result); // bool(false) — ($result = false) or true


function checkA(): bool
{
    echo "A ";
    return false;
}

function checkB(): bool
{
    echo "B ";
    return true;
}

$result = checkA() && checkB(); // prints "A " — checkB() never runs
echo $result ? 'true' : 'false'; // false

echo "\n";

$result = checkB() || checkA(); // prints "B " — checkA() never runs
echo $result ? 'true' : 'false'; // true


// Safe access via short-circuit
$config = null;
$dbHost = $config && $config['db'] && $config['db']['host'];
// Never throws, stops at the first false

$file = 'config.php';
$loaded = file_exists($file) && require $file;

// For defaults, prefer ?? over short-circuit
$name = $_GET['name'] ?? 'Guest';
