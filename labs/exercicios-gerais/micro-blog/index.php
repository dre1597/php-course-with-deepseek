<?php

require_once __DIR__ . '/MicroBlog.php';

$blog = new MicroBlog(__DIR__ . '/posts');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');

    if ($title !== '' && $content !== '' && $author !== '') {
        $blog->createPost($title, $content, $author);
    }

    header('Location: index.php');
    exit;
}

$posts = $blog->listPosts();

require __DIR__ . '/template.php';
