<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/09-renderer.php';

class OutputTest extends TestCase
{
    public function testRendersHTML(): void
    {
        $this->expectOutputString("<h1>Hello World</h1>\n");

        renderTemplate('World');
    }

    public function testCapturesPartialOutput(): void
    {
        echo "Start\n";
        echo "Middle\n";
        echo "End\n";

        $this->expectOutputRegex('/Middle/');
    }
}
