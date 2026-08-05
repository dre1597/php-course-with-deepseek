<?php

namespace App\Util;

echo __LINE__;     // Current line number in the file
echo __FILE__;     // Full path of the file
echo __DIR__;      // Directory of the file (PHP 5.3+)
echo __FUNCTION__; // Current function name
echo __CLASS__;    // Current class name (includes namespace)
echo __METHOD__;   // Current method name (Class::method)
echo __NAMESPACE__;// Current namespace
echo __TRAIT__;    // Current trait name (PHP 5.4+)

function whereAmI(): void
{
    echo "Function: " . __FUNCTION__ . "\n";    // App\Util\whereAmI
    echo "Namespace: " . __NAMESPACE__ . "\n"; // App\Util
    echo "File: " . __FILE__ . "\n";
    echo "Line: " . __LINE__ . "\n";
}

whereAmI();