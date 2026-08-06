<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AssertionsReferenceTest extends TestCase
{
    public function testEquality(): void
    {
        $this->assertEquals(5, 2 + 3);
        $this->assertSame(5, 2 + 3);
    }

    public function testTruthAndFalse(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertNull(null);
        $this->assertNotNull(42);
    }

    public function testStrings(): void
    {
        $this->assertStringContainsString('World', 'Hello World');
        $this->assertMatchesRegularExpression('/^\d{3}\.?\d{3}/', '123.456-78');
    }

    public function testArrays(): void
    {
        $this->assertCount(3, ['a', 'b', 'c']);
        $this->assertContains('PHP', ['PHP', 'JS', 'Go']);
        $this->assertArrayHasKey('name', ['name' => 'Dre']);
    }

    public function testInstanceAndType(): void
    {
        $this->assertInstanceOf(\DateTime::class, new \DateTime());
        $this->assertIsArray([1, 2]);
        $this->assertIsInt(42);
    }
}
