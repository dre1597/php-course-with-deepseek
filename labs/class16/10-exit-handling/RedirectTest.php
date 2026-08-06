<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/10-redirect.php';

class RedirectTest extends TestCase
{
    public function testExitHandlingViaException(): void
    {
        $this->expectException(ExitException::class);
        $this->expectExceptionMessageIs('redirected to /login');

        redirectTestable('/login');
    }
}
