<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/security_headers.php';

class SecurityHeadersTest extends TestCase
{
    public function testIncludesAllDefaultHeaders(): void
    {
        $headers = new SecurityHeaders();
        $all = $headers->getHeaders();

        $this->assertArrayHasKey('X-Content-Type-Options', $all);
        $this->assertArrayHasKey('X-Frame-Options', $all);
        $this->assertArrayHasKey('Referrer-Policy', $all);
        $this->assertArrayHasKey('Permissions-Policy', $all);
        $this->assertArrayHasKey('Strict-Transport-Security', $all);
    }

    public function testDefaultValuesAreCorrect(): void
    {
        $all = new SecurityHeaders()->getHeaders();

        $this->assertSame('nosniff', $all['X-Content-Type-Options']);
        $this->assertSame('DENY', $all['X-Frame-Options']);
        $this->assertSame('strict-origin-when-cross-origin', $all['Referrer-Policy']);
        $this->assertStringContainsString('camera=()', $all['Permissions-Policy']);
        $this->assertStringContainsString('max-age=31536000', $all['Strict-Transport-Security']);
    }

    public function testSetAddsNewHeader(): void
    {
        $headers = new SecurityHeaders();
        $headers->set('X-Custom', 'my-value');

        $all = $headers->getHeaders();
        $this->assertArrayHasKey('X-Custom', $all);
        $this->assertSame('my-value', $all['X-Custom']);
    }

    public function testSetOverwritesExistingHeader(): void
    {
        $headers = new SecurityHeaders();
        $headers->set('X-Frame-Options', 'SAMEORIGIN');

        $all = $headers->getHeaders();
        $this->assertSame('SAMEORIGIN', $all['X-Frame-Options']);
    }

    public function testRemoveDeletesHeader(): void
    {
        $headers = new SecurityHeaders();
        $headers->remove('X-Frame-Options');

        $all = $headers->getHeaders();
        $this->assertArrayNotHasKey('X-Frame-Options', $all);
    }

    public function testRemoveNonExistentHeaderDoesNothing(): void
    {
        $headers = new SecurityHeaders();
        $headers->remove('Non-Existent');

        $all = $headers->getHeaders();
        $this->assertCount(5, $all);
    }

    public function testSetAndRemoveAreFluent(): void
    {
        $headers = new SecurityHeaders();

        $this->assertSame($headers, $headers->set('X-A', '1'));
        $this->assertSame($headers, $headers->remove('X-A'));
    }

    public function testGetHeadersReturnsArray(): void
    {
        $all = new SecurityHeaders()->getHeaders();

        $this->assertIsArray($all);
    }

    public function testSendDoesNotThrowException(): void
    {
        $headers = new SecurityHeaders();

        $this->expectNotToPerformAssertions();
        $headers->send();
    }

    public function testCustomHeadersOverrideDefaults(): void
    {
        $headers = new SecurityHeaders([
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Custom'        => 'custom',
        ]);

        $all = $headers->getHeaders();

        $this->assertSame('SAMEORIGIN', $all['X-Frame-Options']);
        $this->assertSame('custom', $all['X-Custom']);
        $this->assertSame('nosniff', $all['X-Content-Type-Options']);
    }
}
