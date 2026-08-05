<?php

declare(strict_types=1);

$name = $argv[1] ?? 'Mundo';

echo "Olá, {$name}!\n";
echo "PHP versão: " . PHP_VERSION . "\n";
echo "Sistema: " . PHP_OS . "\n";
