<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/dir-monitor.php';

class DirMonitorTest extends TestCase
{
    private string $tempDir;
    private string $snapshotFile;
    private string $workDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/monitor_test_' . uniqid() . '/';
        $this->workDir = $this->tempDir . 'watched/';
        $this->snapshotFile = $this->tempDir . 'snapshot.json';

        mkdir($this->tempDir, 0777, true);
        mkdir($this->workDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursive($this->tempDir);
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createFile(string $name, ?int $mtime = null): void
    {
        $path = $this->workDir . $name;
        file_put_contents($path, "content of $name");

        if ($mtime !== null) {
            touch($path, $mtime);
        }

        clearstatcache(true, $path);
    }

    private function modifyFile(string $name): void
    {
        $path = $this->workDir . $name;
        sleep(1);
        file_put_contents($path, 'modified content ' . time());
        clearstatcache(true, $path);
    }

    private function deleteFile(string $name): void
    {
        unlink($this->workDir . $name);
    }

    public function testFirstRunFlagsAllFilesAsNew(): void
    {
        $this->createFile('a.txt');
        $this->createFile('b.php');
        $this->createFile('c.log');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(3, $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(0, $result['removed']);
        $this->assertContains('a.txt', $result['new']);
        $this->assertContains('b.php', $result['new']);
        $this->assertContains('c.log', $result['new']);
    }

    public function testNewFileIsDetected(): void
    {
        $this->createFile('existing.txt');
        monitorDirectory($this->workDir, $this->snapshotFile);

        $this->createFile('fresh.log');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(1, $result['new']);
        $this->assertContains('fresh.log', $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(0, $result['removed']);
    }

    public function testModifiedFileIsDetected(): void
    {
        $this->createFile('data.csv');
        monitorDirectory($this->workDir, $this->snapshotFile);

        $this->modifyFile('data.csv');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(0, $result['new']);
        $this->assertCount(1, $result['modified']);
        $this->assertContains('data.csv', $result['modified']);
        $this->assertCount(0, $result['removed']);
    }

    public function testRemovedFileIsDetected(): void
    {
        $this->createFile('old.txt');
        $this->createFile('keep.txt');
        monitorDirectory($this->workDir, $this->snapshotFile);

        $this->deleteFile('old.txt');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(0, $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(1, $result['removed']);
        $this->assertContains('old.txt', $result['removed']);
    }

    public function testNoChangesReturnsEmptyDiff(): void
    {
        $this->createFile('stable.txt');
        monitorDirectory($this->workDir, $this->snapshotFile);

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(0, $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(0, $result['removed']);
    }

    public function testSnapshotFileIsExcludedFromMonitoring(): void
    {
        $snapshotInside = $this->workDir . 'my_snapshot.json';
        file_put_contents($snapshotInside, '{}');
        touch($snapshotInside, time() - 3600);
        $this->createFile('legit.txt');

        $result = monitorDirectory($this->workDir, $snapshotInside);

        $this->assertCount(1, $result['new']);
        $this->assertSame('legit.txt', $result['new'][0]);
    }

    public function testMixedChangesAllAtOnce(): void
    {
        $this->createFile('unchanged.txt');
        $this->createFile('will_delete.txt');
        $this->createFile('will_modify.txt');
        monitorDirectory($this->workDir, $this->snapshotFile);

        $this->deleteFile('will_delete.txt');
        $this->modifyFile('will_modify.txt');
        $this->createFile('brand_new.txt');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertContains('brand_new.txt', $result['new']);
        $this->assertContains('will_modify.txt', $result['modified']);
        $this->assertContains('will_delete.txt', $result['removed']);
        $this->assertCount(1, $result['new']);
        $this->assertCount(1, $result['modified']);
        $this->assertCount(1, $result['removed']);
    }

    public function testSnapshotIsUpdatedAfterRun(): void
    {
        $this->createFile('x.txt', 1000000000);
        monitorDirectory($this->workDir, $this->snapshotFile);

        $snapshot = json_decode(file_get_contents($this->snapshotFile), true);
        $this->assertArrayHasKey('x.txt', $snapshot);
        $this->assertSame(1000000000, $snapshot['x.txt']);
    }

    public function testEmptyDirectoryFirstRun(): void
    {
        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(0, $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(0, $result['removed']);
        $this->assertFileExists($this->snapshotFile);
    }

    public function testDirectoryDoesNotExistReturnsEmpty(): void
    {
        $result = monitorDirectory($this->workDir . 'nope/', $this->snapshotFile);

        $this->assertCount(0, $result['new']);
        $this->assertCount(0, $result['modified']);
        $this->assertCount(0, $result['removed']);
    }

    public function testSubdirectoriesAreNotTracked(): void
    {
        mkdir($this->workDir . 'sub/');
        $this->createFile('root.txt');
        file_put_contents($this->workDir . 'sub/nested.txt', 'hidden');

        $result = monitorDirectory($this->workDir, $this->snapshotFile);

        $this->assertCount(1, $result['new']);
        $this->assertContains('root.txt', $result['new']);
    }

    public function testFileMtimeIsStoredInSnapshot(): void
    {
        $targetMtime = 1700000000;
        $this->createFile('timestamped.txt', $targetMtime);
        monitorDirectory($this->workDir, $this->snapshotFile);

        $snapshot = json_decode(file_get_contents($this->snapshotFile), true);
        $this->assertSame($targetMtime, $snapshot['timestamped.txt']);
    }
}
