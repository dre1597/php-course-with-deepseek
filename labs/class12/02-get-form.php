<!-- search.php -->
<form method="get" action="search.php">
    <label for="q">Search:</label>
    <input type="text" name="q" id="q"
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Search</button>
</form>

<?php
if (!empty($_GET['q'])) {
    $searchTerm = htmlspecialchars($_GET['q']);
    echo "<p>You searched for: <strong>{$searchTerm}</strong></p>\n";
}
?>
