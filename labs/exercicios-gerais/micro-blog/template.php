<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>Micro Blog</title>
  </head>
  <body>
    <h1>Micro Blog</h1>

    <h2>Novo post</h2>

    <form action="index.php" method="post">
      <label for="title">Título:</label>
      <input type="text" id="title" name="title" required>

      <label for="content">Conteúdo:</label>
      <textarea id="content" name="content" required></textarea>

      <label for="author">Autor:</label>
      <input type="text" id="author" name="author" required>

      <button type="submit">Postar</button>
    </form>

    <h2>Posts</h2>

    <ul>
      <?php foreach ($posts as $post) : ?>
        <li>
          <h3><?= htmlspecialchars($post['title']) ?></h3>
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <p>Postado por: <?= htmlspecialchars($post['author']) ?></p>
          <p>Em: <?= htmlspecialchars($post['date']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </body>
</html>
