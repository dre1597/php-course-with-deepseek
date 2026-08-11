<?php

function safe(mixed $value, string $context = 'html'): string
{
    return match ($context) {
        'attr' => htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'js'   => json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
        'url'  => urlencode((string)$value),
        default => htmlspecialchars((string)$value, ENT_NOQUOTES | ENT_HTML5, 'UTF-8'),
    };
}
