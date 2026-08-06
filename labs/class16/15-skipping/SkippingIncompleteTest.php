<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SkippingIncompleteTest extends TestCase
{
    public function testFeatureNotImplementedYet(): void
    {
        $this->markTestIncomplete("Waiting for API v2");
    }

    public function testOnlyOnPHP85(): void
    {
        if (PHP_VERSION_ID < 80500) {
            $this->markTestSkipped("Requires PHP 8.5+");
        }

        $this->assertTrue(true);
    }
}
