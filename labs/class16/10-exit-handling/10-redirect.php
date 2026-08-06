<?php

declare(strict_types=1);

class ExitException extends \RuntimeException {}

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

function redirectTestable(string $url): never
{
    throw new ExitException("redirected to {$url}");
}
