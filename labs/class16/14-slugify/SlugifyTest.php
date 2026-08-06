<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/14-slugify.php';

class SlugifyTest extends TestCase
{
    #[DataProvider("slugProvider")]
    public function testSlugify(string $input, string $expected): void
    {
        $this->assertEquals($expected, slugify($input));
    }

    public static function slugProvider(): array
    {
        return [
            "simple"                   => ["Hello World", "hello-world"],
            "accents unhandled"        => ["caçamba", "ca-amba"],
            "leading trailing hyphens" => ["--hello--", "hello"],
            "multiple spaces"          => ["php   is   great", "php-is-great"],
            "already a slug"           => ["my-post", "my-post"],
            "numbers"                  => ["PHP 8.5 rocks", "php-8-5-rocks"],
        ];
    }
}
