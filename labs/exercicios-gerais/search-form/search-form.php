<?php
$data = [
    'PHP',
    'JavaScript',
    'TypeScript',
    'Python',
    'Java',
    'Ruby',
    'Go',
    'Rust',
    'C#',
    'Kotlin',
    'Swift',
    'Dart',
    'Elixir',
    'Scala',
    'Lua',
];
$term = trim($_GET['q'] ?? '');
$results = array_filter($data, fn($item) => stripos($item, $term) !== false);
?>

<form method="get">
  <label for="q">Search:</label>
  <input type="text"
         name="q" id="q"
          value="<?= htmlspecialchars($term) ?>"
  >
  <button type="submit">Search</button>
  <?php if (empty($results)): ?>
    <p>Nenhum resultado encontrado.</p>
  <?php else: ?>
  <ul>
      <?php foreach ($results as $item): ?>
        <li><?= htmlspecialchars($item) ?></li>
      <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</form>
