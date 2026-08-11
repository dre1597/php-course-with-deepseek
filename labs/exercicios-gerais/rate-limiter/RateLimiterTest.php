<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/RateLimiter.php';

class RateLimiterTest extends TestCase
{
    private string $tempDirectory;

    protected function setUp(): void
    {
        $this->tempDirectory = sys_get_temp_dir() . '/rate-limiter-test-' . uniqid();
        mkdir($this->tempDirectory, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursively($this->tempDirectory);
    }

    public function testSingleAttemptAllowed(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-1', '10.0.0.1');

        $this->assertTrue($rateLimiter->attempt());
    }

    public function testFiveSessionAttemptsAllowed(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-1', '10.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($rateLimiter->attempt());
        }
    }

    public function testSixthSessionAttemptBlocked(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-block', '10.0.0.2');

        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->attempt();
        }

        $this->assertFalse($rateLimiter->attempt());
    }

    public function testIpLimitBlocksAfter20Attempts(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $rateLimiter = new RateLimiter($this->tempDirectory, 'unique-session-' . $i, '10.0.0.99');
            $this->assertTrue($rateLimiter->attempt());
        }

        $rateLimiter = new RateLimiter($this->tempDirectory, 'unique-session-final', '10.0.0.99');
        $this->assertFalse($rateLimiter->attempt());
    }

    public function testDifferentSessionsShareIpLimit(): void
    {
        $sessionAlpha = new RateLimiter($this->tempDirectory, 'session-A', '10.0.0.3');
        $sessionBeta  = new RateLimiter($this->tempDirectory, 'session-B', '10.0.0.3');

        for ($i = 0; $i < 10; $i++) {
            $sessionAlpha->attempt();
        }
        for ($i = 0; $i < 10; $i++) {
            $sessionBeta->attempt();
        }

        $this->assertFalse($sessionBeta->attempt());
    }

    public function testDifferentIpsDontAffectEachOther(): void
    {
        for ($i = 0; $i < 19; $i++) {
            $rateLimiterFromFirstIp = new RateLimiter($this->tempDirectory, 'ip-a-' . $i, '10.0.0.4');
            $rateLimiterFromFirstIp->attempt();
        }

        $rateLimiterFromFirstIp  = new RateLimiter($this->tempDirectory, 'ip-a-19', '10.0.0.4');
        $rateLimiterFromSecondIp = new RateLimiter($this->tempDirectory, 'ip-b', '10.0.0.5');

        $this->assertTrue($rateLimiterFromSecondIp->attempt());
        $this->assertTrue($rateLimiterFromFirstIp->attempt());
    }

    public function testGetRemainingInitialValues(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'fresh-session', '10.0.0.6');

        $remainingAttempts = $rateLimiter->getRemaining();

        $this->assertSame(['session' => 5, 'ip' => 20], $remainingAttempts);
    }

    public function testGetRemainingDecrementsAfterAttempts(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-decrement', '10.0.0.7');
        $rateLimiter->attempt();
        $rateLimiter->attempt();

        $remainingAttempts = $rateLimiter->getRemaining();

        $this->assertSame(['session' => 3, 'ip' => 18], $remainingAttempts);
    }

    public function testGetRetryAfterReturnsZeroWhenNotBlocked(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-ok', '10.0.0.8');
        $rateLimiter->attempt();

        $this->assertSame(0, $rateLimiter->getRetryAfter());
    }

    public function testGetRetryAfterReturnsPositiveWhenBlocked(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-retry', '10.0.0.9');

        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->attempt();
        }

        $retryAfterSeconds = $rateLimiter->getRetryAfter();
        $this->assertGreaterThan(0, $retryAfterSeconds);
        $this->assertLessThanOrEqual(900, $retryAfterSeconds);
    }

    public function testResetClearsAllData(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-clear', '10.0.0.10');

        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->attempt();
        }

        $this->assertFalse($rateLimiter->attempt());

        $rateLimiter->reset();

        $this->assertTrue($rateLimiter->attempt());
        $this->assertSame(['session' => 4, 'ip' => 19], $rateLimiter->getRemaining());
    }

    public function testSessionLimitDoesNotAffectIpLimitCount(): void
    {
        $rateLimiter = new RateLimiter($this->tempDirectory, 'session-separate', '10.0.0.11');

        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->attempt();
        }

        $remainingAttempts = $rateLimiter->getRemaining();

        $this->assertSame(['session' => 0, 'ip' => 15], $remainingAttempts);
    }

    public function testPersistenceAcrossInstances(): void
    {
        $firstInstance = new RateLimiter($this->tempDirectory, 'persistent-session', '10.0.0.12');
        $firstInstance->attempt();
        $firstInstance->attempt();

        $secondInstance = new RateLimiter($this->tempDirectory, 'persistent-session', '10.0.0.12');
        $remainingAttempts = $secondInstance->getRemaining();

        $this->assertSame(['session' => 3, 'ip' => 18], $remainingAttempts);
    }

    private function removeDirectoryRecursively(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = array_diff(scandir($directory), ['.', '..']);
        foreach ($entries as $entry) {
            $entryPath = $directory . '/' . $entry;
            is_dir($entryPath) ? $this->removeDirectoryRecursively($entryPath) : unlink($entryPath);
        }
        rmdir($directory);
    }
}
