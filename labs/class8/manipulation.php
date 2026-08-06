<?php

echo strlen('PHP 8.5');   // 7
echo strlen('café');      // 5  — WARNING: counts bytes, not characters!

$phrase = 'The quick brown fox jumps over the lazy dog';

$pos = strpos($phrase, 'fox');
echo $pos; // 16 (zero-based position)

$pos2 = strpos($phrase, 'dog');
echo $pos2; // 40

// Not found returns false
$notFound = strpos($phrase, 'Python');
var_dump($notFound); // bool(false)

$str = 'PHP is cool';

if (strpos($str, 'PHP') !== false) {
    echo 'Found!';
}

echo stripos('Hello WORLD', 'world'); // 6

$text = 'The black cat jumped over the white cat';
$new = str_replace('cat', 'dog', $text);
echo $new; // The black dog jumped over the white dog

// Multiple replacements with arrays:
$searches = ['cat', 'black', 'white'];
$replacements = ['bird', 'blue', 'yellow'];
echo str_replace($searches, $replacements, $text);
// The blue bird jumped over the yellow bird

echo str_ireplace('PHP', 'JavaScript', 'php is cool and PHP too');
// JavaScript is cool and JavaScript too

$text = 'PHP 8.5 - New Features';

echo substr($text, 0, 3);   // PHP (from 0, 3 characters)
echo substr($text, 4, 3);   // 8.5
echo substr($text, -12);     // New Features (last 12 characters)
echo substr($text, 4, -5);  // 8.5 - New Fea (remove 5 from end)

$dirty = "   \t  Hello, World!  \n  ";

echo trim($dirty);   // "Hello, World!"
echo ltrim($dirty);  // "Hello, World!  \n  "
echo rtrim($dirty);  // "   \t  Hello, World!"

// Remove specific characters:
$value = '...R$ 99,90...';
echo trim($value, '.');            // "R$ 99,90"
echo trim($value, '.R$ ');         // "99,90"

$name = 'john smith';

echo strtoupper($name);    // JOHN SMITH
echo strtolower($name);    // john smith
echo ucfirst($name);       // John smith
echo ucwords($name);       // John Smith
echo lcfirst('PHP');       // pHP

// IMPORTANT: these functions do NOT handle UTF-8 correctly.
// For accented characters, use mb_* equivalents (see below).
echo ucfirst('árvore');    // árvore (does not convert 'á' to 'Á')
