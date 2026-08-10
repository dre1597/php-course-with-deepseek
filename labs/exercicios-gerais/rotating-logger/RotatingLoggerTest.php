<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/RotatingLogger.php';

class RotatingLoggerTest extends TestCase
{
    private string $tempDir;
    private string $logPath;

    private const int SMALL_SIZE = 200;
    private const int TINY_SIZE = 80;
    private const int SMALL_FILES = 3;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/logger_test_' . uniqid() . '/';
        mkdir($this->tempDir, 0777, true);
        $this->logPath = $this->tempDir . 'app.log';
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '*');
        if (is_dir($this->tempDir . 'deep')) {
            $files = array_merge($files, $this->rglob($this->tempDir . 'deep'));
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->tempDir . 'deep/nested/logs')) {
            rmdir($this->tempDir . 'deep/nested/logs');
        }
        if (is_dir($this->tempDir . 'deep/nested')) {
            rmdir($this->tempDir . 'deep/nested');
        }
        if (is_dir($this->tempDir . 'deep')) {
            rmdir($this->tempDir . 'deep');
        }
        rmdir($this->tempDir);
    }

    private function rglob(string $dir): array
    {
        $files = [];
        foreach (glob($dir . '/*') as $path) {
            if (is_dir($path)) {
                $files = array_merge($files, $this->rglob($path));
            } else {
                $files[] = $path;
            }
        }

        return $files;
    }

    private function readLines(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        return $path
                |> file_get_contents(...)
                |> (fn($x) => explode("\n", $x))
                |> (fn($x) => array_filter($x, fn(string $line) => $line !== ''))
                |> array_values(...);
    }

    private function countRotatedFiles(): int
    {
        return count(glob($this->tempDir . 'app.log.*'));
    }

    private function buildLogger(int $maxSize = self::SMALL_SIZE, int $maxFiles = self::SMALL_FILES): RotatingLogger
    {
        return new RotatingLogger($this->logPath, $maxSize, $maxFiles);
    }

    public function testLogWritesEntryToFile(): void
    {
        $logger = $this->buildLogger();
        $logger->log('user logged in');

        $lines = $this->readLines($this->logPath);
        $this->assertCount(1, $lines);
        $this->assertStringEndsWith('user logged in', $lines[0]);
    }

    public function testLogIncludesLevelTag(): void
    {
        $logger = $this->buildLogger();
        $logger->log('something went wrong', 'ERROR');

        $lines = $this->readLines($this->logPath);
        $this->assertStringContainsString('ERROR', $lines[0]);
    }

    public function testDefaultLevelIsInfo(): void
    {
        $logger = $this->buildLogger();
        $logger->log('routine check');

        $lines = $this->readLines($this->logPath);
        $this->assertStringContainsString('INFO', $lines[0]);
    }

    public function testRotationWhenFileExceedsMaxSize(): void
    {
        $logger = $this->buildLogger();
        $message = str_repeat('x', 80);

        for ($i = 0; $i < 10; $i++) {
            $logger->log($message);
        }

        $logger->log('final');

        $this->assertGreaterThan(0, $this->countRotatedFiles());
        $this->assertFileExists($this->logPath);
    }

    public function testRotatedFileContainsPreviousContent(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 15);
        $logger->log('first entry');

        for ($i = 0; $i < 10; $i++) {
            $logger->log(str_repeat('z', 60));
        }

        $rotated = glob($this->tempDir . 'app.log.*');
        $this->assertNotEmpty($rotated);

        $allRotatedContent = '';
        foreach ($rotated as $file) {
            $allRotatedContent .= file_get_contents($file);
        }

        $this->assertStringContainsString('first entry', $allRotatedContent);
    }

    public function testNewEntriesGoToFreshFileAfterRotation(): void
    {
        $logger = $this->buildLogger();

        for ($i = 0; $i < 10; $i++) {
            $logger->log(str_repeat('a', 80));
        }

        $this->assertGreaterThan(0, $this->countRotatedFiles());

        $logger->log('fresh entry after rotation');

        $freshLines = $this->readLines($this->logPath);
        $this->assertNotEmpty($freshLines);
        $this->assertStringContainsString('fresh entry after rotation', end($freshLines));
    }

    public function testMaxOldFilesLimitIsEnforced(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 2);

        for ($i = 0; $i < 30; $i++) {
            $logger->log("entry $i " . str_repeat('b', 60));
        }

        $this->assertLessThanOrEqual(2, $this->countRotatedFiles());
    }

    public function testCustomMaxSizeViaConstructor(): void
    {
        $logger = $this->buildLogger(600, 5);

        for ($i = 0; $i < 5; $i++) {
            $logger->log(str_repeat('c', 50));
        }

        $this->assertSame(0, $this->countRotatedFiles());

        for ($i = 0; $i < 15; $i++) {
            $logger->log(str_repeat('c', 80));
        }

        $this->assertGreaterThan(0, $this->countRotatedFiles());
    }

    public function testCustomMaxFilesViaConstructor(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 4);

        for ($i = 0; $i < 30; $i++) {
            $logger->log("entry $i " . str_repeat('d', 60));
        }

        $this->assertLessThanOrEqual(4, $this->countRotatedFiles());
    }

    public function testCreatesLogDirectoryIfNotExists(): void
    {
        $deepDir = $this->tempDir . 'deep/nested/logs/';
        $deepPath = $deepDir . 'deep.log';
        $logger = new RotatingLogger($deepPath, self::SMALL_SIZE, self::SMALL_FILES);

        $logger->log('hello from deep');

        $this->assertDirectoryExists($deepDir);
        $this->assertFileExists($deepPath);
    }

    public function testMultipleLogLevels(): void
    {
        $logger = $this->buildLogger();

        $logger->log('info message');
        $logger->log('debug trace', 'DEBUG');
        $logger->log('warning alert', 'WARNING');
        $logger->log('critical failure', 'ERROR');

        $lines = $this->readLines($this->logPath);
        $this->assertCount(4, $lines);
        $this->assertStringContainsString('INFO', $lines[0]);
        $this->assertStringContainsString('DEBUG', $lines[1]);
        $this->assertStringContainsString('WARNING', $lines[2]);
        $this->assertStringContainsString('ERROR', $lines[3]);
    }

    public function testSingleEntryLargerThanMaxSizeTriggersImmediateRotation(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 3);

        $logger->log(str_repeat('M', 120));

        $this->assertSame(1, $this->countRotatedFiles());

        $logger->log('next entry');
        $this->assertFileExists($this->logPath);
        $this->assertStringContainsString('next entry', file_get_contents($this->logPath));
    }

    public function testEmptyMessageDoesNotBreakLogger(): void
    {
        $logger = $this->buildLogger();
        $logger->log('');
        $logger->log('valid entry');

        $lines = $this->readLines($this->logPath);
        $this->assertCount(2, $lines);
    }

    public function testLogSequenceIsMaintainedAcrossRotations(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 20);
        $entries = [];

        for ($i = 1; $i <= 20; $i++) {
            $msg = "entry $i " . str_repeat('e', 50);
            $entries[] = $msg;
            $logger->log($msg);
        }

        $allFiles = glob($this->tempDir . 'app.log*');
        usort($allFiles, fn(string $a, string $b) => filemtime($a) <=> filemtime($b));

        $allContent = '';
        foreach ($allFiles as $file) {
            $allContent .= file_get_contents($file);
        }

        foreach ($entries as $entry) {
            $this->assertStringContainsString($entry, $allContent);
        }
    }

    public function testMultipleConsecutiveRotations(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 5);

        for ($i = 0; $i < 40; $i++) {
            $logger->log("msg $i " . str_repeat('f', 60));
        }

        $rotated = $this->countRotatedFiles();
        $this->assertGreaterThan(0, $rotated);
        $this->assertLessThanOrEqual(5, $rotated);
    }

    public function testMaxFilesCannotBeZero(): void
    {
        $logger = $this->buildLogger(self::TINY_SIZE, 0);

        for ($i = 0; $i < 20; $i++) {
            $logger->log(str_repeat('g', 60));
        }

        $this->assertSame(0, $this->countRotatedFiles());
    }

    public function testLogIncludesTimestamp(): void
    {
        $logger = $this->buildLogger();
        $logger->log('timestamped entry');

        $content = file_get_contents($this->logPath);
        $this->assertMatchesRegularExpression(
            '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}]/',
            $content,
        );
    }
}
