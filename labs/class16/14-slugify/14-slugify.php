<?php

declare(strict_types=1);

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace("/[^a-z0-9]+/", "-", $text);
    return trim($text, "-");
}
