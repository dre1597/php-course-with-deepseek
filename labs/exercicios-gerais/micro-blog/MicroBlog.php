<?php

class MicroBlog
{
    public function __construct(private readonly string $postsDirectory)
    {
        if (!is_dir($this->postsDirectory)) {
            mkdir($this->postsDirectory, 0755, true);
        }
    }

    public function createPost(string $title, string $content, string $author): array
    {
        $slug = $this->slugify($title);
        $post = [
            'title' => $title,
            'content' => $content,
            'date' => date('Y-m-d H:i:s'),
            'author' => $author,
        ];

        $json = json_encode($post, JSON_UNESCAPED_UNICODE);
        file_put_contents(
            $this->postsDirectory . '/' . $slug . '.json',
            $json,
            LOCK_EX
        );

        $post['slug'] = $slug;

        return $post;
    }

    public function listPosts(): array
    {
        $files = glob($this->postsDirectory . '/*.json');
        $posts = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $post = json_decode($content, true);

            if (!is_array($post)) {
                continue;
            }

            $post['slug'] = pathinfo($file, PATHINFO_FILENAME);
            $posts[] = $post;
        }

        usort($posts, fn($a, $b) => ($b['date'] ?? '') <=> ($a['date'] ?? ''));

        return $posts;
    }

    public function findBySlug(string $slug): ?array
    {
        $file = $this->postsDirectory . '/' . $slug . '.json';

        if (!is_file($file)) {
            return null;
        }

        $post = json_decode(file_get_contents($file), true);

        if (!is_array($post)) {
            return null;
        }

        $post['slug'] = $slug;

        return $post;
    }

    private function slugify(string $title): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title)
                |> strtolower(...)
                |> (fn($x) => preg_replace('/[^a-z0-9]+/', '-', $x))
                |> (fn($x) => trim($x, '-'));

        return $slug !== '' ? $slug : 'post';
    }
}
