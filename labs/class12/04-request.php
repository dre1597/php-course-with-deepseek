<?php
// Avoid using $_REQUEST in production — it's unclear where the data comes from.
$searchTerm = $_REQUEST['searchTerm'] ?? '';

// Prefer being explicit:
$searchTerm = $_GET['searchTerm'] ?? $_POST['searchTerm'] ?? '';
