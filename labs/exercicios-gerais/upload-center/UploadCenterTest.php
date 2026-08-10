<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/upload-center.php';

class UploadCenterTest extends TestCase
{
    private string $tempDir;
    private string $uploadDir;
    private string $metadataFile;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/upload_test_' . uniqid() . '/';
        mkdir($this->tempDir, 0777, true);
        $this->uploadDir = $this->tempDir . 'uploads/';
        mkdir($this->uploadDir, 0777, true);
        $this->metadataFile = $this->tempDir . 'metadata.json';
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

    private function createImageFile(string $filename): string
    {
        $path = $this->tempDir . $filename;
        $img = imagecreatetruecolor(10, 10);
        imagecolorallocate($img, 255, 0, 0);
        imagejpeg($img, $path);

        return $path;
    }

    private function createTextFile(string $filename): string
    {
        $path = $this->tempDir . $filename;
        file_put_contents($path, 'this is not an image');

        return $path;
    }

    private function buildFilesArray(array $specs): array
    {
        $files = [
            'name'     => [],
            'type'     => [],
            'tmp_name' => [],
            'error'    => [],
            'size'     => [],
        ];

        foreach ($specs as $spec) {
            $files['name'][]     = $spec['name'];
            $files['type'][]     = $spec['type'] ?? mime_content_type($spec['tmp_name']);
            $files['tmp_name'][] = $spec['tmp_name'];
            $files['error'][]    = $spec['error'] ?? UPLOAD_ERR_OK;
            $files['size'][]     = $spec['size'] ?? filesize($spec['tmp_name']);
        }

        return $files;
    }

    private function readMetadata(): array
    {
        if (!file_exists($this->metadataFile)) {
            return [];
        }

        return json_decode(file_get_contents($this->metadataFile), true);
    }

    public function testSingleValidImageUpload(): void
    {
        $tmpPath = $this->createImageFile('photo.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'photo.jpg', 'tmp_name' => $tmpPath],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(1, $result['uploaded']);
        $this->assertCount(0, $result['errors']);
        $this->assertSame('photo.jpg', $result['uploaded'][0]['original_name']);
        $this->assertStringEndsWith('.jpg', $result['uploaded'][0]['saved_name']);
        $this->assertTrue(file_exists($this->uploadDir . $result['uploaded'][0]['saved_name']));
    }

    public function testInvalidFileTypeIsRejected(): void
    {
        $tmpPath = $this->createTextFile('document.txt');
        $files = $this->buildFilesArray([
            ['name' => 'document.txt', 'tmp_name' => $tmpPath],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(0, $result['uploaded']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('type', $result['errors'][0]);
    }

    public function testFileExceedingMaxSizeIsRejected(): void
    {
        $tmpPath = $this->createImageFile('huge.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'huge.jpg', 'tmp_name' => $tmpPath, 'size' => 3 * 1024 * 1024],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(0, $result['uploaded']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('size', $result['errors'][0]);
    }

    public function testMultipleValidImages(): void
    {
        $tmp1 = $this->createImageFile('a.jpg');
        $tmp2 = $this->createImageFile('b.png');
        $tmp3 = $this->createImageFile('c.gif');
        $files = $this->buildFilesArray([
            ['name' => 'a.jpg', 'tmp_name' => $tmp1],
            ['name' => 'b.png', 'tmp_name' => $tmp2],
            ['name' => 'c.gif', 'tmp_name' => $tmp3],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(3, $result['uploaded']);
        $this->assertCount(0, $result['errors']);
    }

    public function testEmptyUpload(): void
    {
        $files = $this->buildFilesArray([]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(0, $result['uploaded']);
        $this->assertCount(0, $result['errors']);
    }

    public function testMetadataIsAccumulatedInJson(): void
    {
        $tmp1 = $this->createImageFile('first.jpg');
        $files1 = $this->buildFilesArray([
            ['name' => 'first.jpg', 'tmp_name' => $tmp1],
        ]);

        processUploads($files1, $this->uploadDir, $this->metadataFile);

        $tmp2 = $this->createImageFile('second.png');
        $files2 = $this->buildFilesArray([
            ['name' => 'second.png', 'tmp_name' => $tmp2],
        ]);

        processUploads($files2, $this->uploadDir, $this->metadataFile);

        $metadata = $this->readMetadata();
        $this->assertCount(2, $metadata);
        $this->assertSame('first.jpg', $metadata[0]['original_name']);
        $this->assertSame('second.png', $metadata[1]['original_name']);
    }

    public function testUniqueFilenamesGenerated(): void
    {
        $tmp1 = $this->createImageFile('same_name.jpg');
        $tmp2 = $this->createImageFile('same_name_again.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'same.jpg', 'tmp_name' => $tmp1],
            ['name' => 'same.jpg', 'tmp_name' => $tmp2],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(2, $result['uploaded']);
        $this->assertNotSame(
            $result['uploaded'][0]['saved_name'],
            $result['uploaded'][1]['saved_name'],
        );
        $this->assertStringEndsWith('.jpg', $result['uploaded'][0]['saved_name']);
        $this->assertStringEndsWith('.jpg', $result['uploaded'][1]['saved_name']);
    }

    public function testMixedValidAndInvalidFiles(): void
    {
        $imgTmp = $this->createImageFile('valid.jpg');
        $txtTmp = $this->createTextFile('invalid.txt');
        $bigTmp = $this->createImageFile('big.jpg');

        $files = $this->buildFilesArray([
            ['name' => 'valid.jpg',   'tmp_name' => $imgTmp],
            ['name' => 'invalid.txt', 'tmp_name' => $txtTmp],
            ['name' => 'big.jpg',     'tmp_name' => $bigTmp, 'size' => 5 * 1024 * 1024],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(1, $result['uploaded']);
        $this->assertCount(2, $result['errors']);
        $this->assertSame('valid.jpg', $result['uploaded'][0]['original_name']);
    }

    public function testUploadErrorIsReported(): void
    {
        $tmpPath = $this->createImageFile('broken.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'broken.jpg', 'tmp_name' => $tmpPath, 'error' => UPLOAD_ERR_NO_FILE],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $this->assertCount(0, $result['uploaded']);
        $this->assertCount(1, $result['errors']);
    }

    public function testMetadataContainsAllRequiredFields(): void
    {
        $tmpPath = $this->createImageFile('fields.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'fields.jpg', 'tmp_name' => $tmpPath],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $entry = $result['uploaded'][0];
        $this->assertArrayHasKey('original_name', $entry);
        $this->assertArrayHasKey('saved_name', $entry);
        $this->assertArrayHasKey('type', $entry);
        $this->assertArrayHasKey('size', $entry);
        $this->assertArrayHasKey('uploaded_at', $entry);
    }

    public function testSavedFileHasCorrectContent(): void
    {
        $tmpPath = $this->createImageFile('content.jpg');
        $originalHash = md5_file($tmpPath);
        $files = $this->buildFilesArray([
            ['name' => 'content.jpg', 'tmp_name' => $tmpPath],
        ]);

        $result = processUploads($files, $this->uploadDir, $this->metadataFile);

        $savedPath = $this->uploadDir . $result['uploaded'][0]['saved_name'];
        $this->assertSame($originalHash, md5_file($savedPath));
    }

    public function testUploadDirIsCreatedIfNotExists(): void
    {
        $nonExistentDir = $this->tempDir . 'nonexistent/';
        $tmpPath = $this->createImageFile('dir.jpg');
        $files = $this->buildFilesArray([
            ['name' => 'dir.jpg', 'tmp_name' => $tmpPath],
        ]);

        $result = processUploads($files, $nonExistentDir, $this->metadataFile);

        $this->assertCount(1, $result['uploaded']);
        $this->assertDirectoryExists($nonExistentDir);
        $this->assertTrue(file_exists($nonExistentDir . $result['uploaded'][0]['saved_name']));
    }
}
