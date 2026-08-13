<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/MicroBlog.php';

class MicroBlogTest extends TestCase
{
    private string $postsDir;
    private MicroBlog $blog;

    protected function setUp(): void
    {
        $this->postsDir = sys_get_temp_dir() . '/micro_blog_' . uniqid();
        $this->blog = new MicroBlog($this->postsDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->postsDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->postsDir);
    }

    private function writeRawPost(string $fileName, string $content): void
    {
        file_put_contents($this->postsDir . '/' . $fileName, $content);
    }

    public function testCreatePostWritesJsonFile(): void
    {
        $this->blog->createPost('Hello World', 'My first post', 'Ana');

        $this->assertFileExists($this->postsDir . '/hello-world.json');
    }

    public function testCreatePostUsesSlugAsFileName(): void
    {
        $this->blog->createPost('My First Post!', 'content', 'Ana');

        $this->assertFileExists($this->postsDir . '/my-first-post.json');
    }

    public function testCreatePostWithAccentsSlugifies(): void
    {
        $this->blog->createPost('Olá, Mundo Cão!', 'content', 'Ana');

        $this->assertFileExists($this->postsDir . '/ola-mundo-cao.json');
    }

    public function testCreatePostReturnsPostWithSlug(): void
    {
        $post = $this->blog->createPost('Title', 'content', 'author');

        $this->assertSame('title', $post['slug']);
        $this->assertSame('Title', $post['title']);
        $this->assertSame('content', $post['content']);
        $this->assertSame('author', $post['author']);
    }

    public function testCreatePostStoresAllFieldsInJson(): void
    {
        $this->blog->createPost('Título Teste', 'Conteúdo com acentuação', 'João');

        $json = file_get_contents($this->postsDir . '/titulo-teste.json');
        $decoded = json_decode($json, true);

        $this->assertSame('Título Teste', $decoded['title']);
        $this->assertSame('Conteúdo com acentuação', $decoded['content']);
        $this->assertSame('João', $decoded['author']);
        $this->assertArrayHasKey('date', $decoded);
    }

    public function testListPostsReturnsCreatedPosts(): void
    {
        $this->blog->createPost('First', 'one', 'A');
        $this->blog->createPost('Second', 'two', 'B');

        $posts = $this->blog->listPosts();

        $this->assertCount(2, $posts);
    }

    public function testListPostsOnEmptyDirectoryReturnsEmptyArray(): void
    {
        $posts = $this->blog->listPosts();

        $this->assertIsArray($posts);
        $this->assertEmpty($posts);
    }

    public function testListPostsSortsByDateDescending(): void
    {
        $this->writeRawPost('old.json', json_encode([
            'title' => 'Old', 'content' => 'x', 'date' => '2024-01-01 10:00:00', 'author' => 'a',
        ]));
        $this->writeRawPost('new.json', json_encode([
            'title' => 'New', 'content' => 'x', 'date' => '2024-06-15 10:00:00', 'author' => 'a',
        ]));
        $this->writeRawPost('mid.json', json_encode([
            'title' => 'Mid', 'content' => 'x', 'date' => '2024-03-01 10:00:00', 'author' => 'a',
        ]));

        $posts = $this->blog->listPosts();

        $this->assertSame('New', $posts[0]['title']);
        $this->assertSame('Mid', $posts[1]['title']);
        $this->assertSame('Old', $posts[2]['title']);
    }

    public function testListPostsIncludesSlug(): void
    {
        $this->blog->createPost('My Post', 'content', 'a');

        $posts = $this->blog->listPosts();

        $this->assertSame('my-post', $posts[0]['slug']);
    }

    public function testListPostsIgnoresMalformedJson(): void
    {
        $this->writeRawPost('broken.json', '{invalid json');
        $this->blog->createPost('Good Post', 'content', 'a');

        $posts = $this->blog->listPosts();

        $this->assertCount(1, $posts);
        $this->assertSame('Good Post', $posts[0]['title']);
    }

    public function testListPostsIgnoresNonJsonFiles(): void
    {
        $this->writeRawPost('notes.txt', 'not json');
        $this->blog->createPost('Real', 'content', 'a');

        $posts = $this->blog->listPosts();

        $this->assertCount(1, $posts);
    }

    public function testFindBySlugReturnsPost(): void
    {
        $this->blog->createPost('Find Me', 'here', 'Zé');

        $post = $this->blog->findBySlug('find-me');

        $this->assertNotNull($post);
        $this->assertSame('Find Me', $post['title']);
        $this->assertSame('Zé', $post['author']);
    }

    public function testFindBySlugReturnsNullForMissingPost(): void
    {
        $post = $this->blog->findBySlug('does-not-exist');

        $this->assertNull($post);
    }

    public function testFindBySlugReturnsNullForMalformedJson(): void
    {
        $this->writeRawPost('broken.json', 'not json at all');

        $this->assertNull($this->blog->findBySlug('broken'));
    }

    public function testCreatePostWithEmptyTitleUsesDefaultSlug(): void
    {
        $this->blog->createPost('', 'content', 'a');

        $this->assertFileExists($this->postsDir . '/post.json');
    }

    public function testCreatePostWithOnlySpecialCharactersUsesDefaultSlug(): void
    {
        $this->blog->createPost('!!!', 'content', 'a');

        $this->assertFileExists($this->postsDir . '/post.json');
    }

    public function testMultiplePostsWithUniqueTitles(): void
    {
        $this->blog->createPost('Post Um', '1', 'a');
        $this->blog->createPost('Post Dois', '2', 'a');
        $this->blog->createPost('Post Três', '3', 'a');

        $this->assertCount(3, $this->blog->listPosts());
        $this->assertFileExists($this->postsDir . '/post-um.json');
        $this->assertFileExists($this->postsDir . '/post-dois.json');
        $this->assertFileExists($this->postsDir . '/post-tres.json');
    }

    public function testConstructorCreatesDirectoryIfMissing(): void
    {
        $dir = sys_get_temp_dir() . '/micro_blog_missing_' . uniqid();
        new MicroBlog($dir);

        $this->assertDirectoryExists($dir);

        rmdir($dir);
    }

    public function testListPostsPreservesUnicodeContent(): void
    {
        $this->blog->createPost('Unicode', 'こんにちは → olá → مرحبا', 'a');

        $posts = $this->blog->listPosts();

        $this->assertSame('こんにちは → olá → مرحبا', $posts[0]['content']);
    }
}
