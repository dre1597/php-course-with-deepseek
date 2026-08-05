<?php

function addSuffix(string &$text, string $suffix): void
{
    $text .= $suffix;
}

$name = 'PHP';
addSuffix($name, ' 8.5');
echo $name; // PHP 8.5


$config = ['debug' => false, 'cache' => true];

function &getConfig(string $key): mixed
{
    global $config;
    return $config[$key];
}

$debug = &getConfig('debug');
$debug = true;

var_dump($config['debug']); // bool(true)