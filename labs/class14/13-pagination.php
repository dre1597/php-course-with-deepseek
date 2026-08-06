<?php
// Configuração
$currentPage = max(1, (int) ($_GET['pagina'] ?? 1));
$perPage = 10;
$offset = ($currentPage - 1) * $perPage;

// Contar total de registros
$stmt = $pdo->query('SELECT COUNT(*) FROM posts');
$totalRecords = $stmt->fetchColumn();
$totalPages = (int) ceil($totalRecords / $perPage);

// Buscar registros da página atual
$stmt = $pdo->prepare('SELECT * FROM posts ORDER BY created_at DESC LIMIT :limite OFFSET :offset');
$stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

// Exibir resultados
foreach ($posts as $post) {
    echo "<h3>" . htmlspecialchars($post['title']) . "</h3>\n";
}

// Links de navegação
echo "<nav>\n";
for ($i = 1; $i <= $totalPages; $i++) {
    $class = ($i === $currentPage) ? 'class="ativo"' : '';
    echo "<a href='?pagina={$i}' {$class}>{$i}</a> ";
}
echo "</nav>\n";
echo "<p>Mostrando página {$currentPage} de {$totalPages}</p>\n";
