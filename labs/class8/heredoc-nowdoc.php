<?php

$name = 'Charles';
$age = 28;

$html = <<<HTML
<div class="user">
    <h2>Name: {$name}</h2>
    <p>Age: {$age} years</p>
</div>
HTML;

echo $html;

function generateTemplate(): string
{
    $title = 'Welcome';

    return <<<HTML
        <section>
            <h1>{$title}</h1>
            <p>PHP 8.5 is awesome.</p>
        </section>
        HTML; // closing indent: 8 spaces
}
// All lines will have 8 spaces stripped from the left

echo generateTemplate();

$framework = 'Laravel';

$text = <<<'TXT'
In this block, $framework will not be interpolated.
All sequences like \n and \t are treated literally.
Here's a backslash: \\ and a dollar sign: \$var
TXT;

echo $text;
// In this block, $framework will not be interpolated.
// All sequences like \n and \t are treated literally.
// Here's a backslash: \\ and a dollar sign: \$var

$config = <<<'JSON'
    {
        "host": "localhost",
        "port": 3306
    }
    JSON;

echo $config;
