<?php
// Contexto HTML
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Contexto JavaScript (dentro de <script>)
$encodedData = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "<script>var name = {$encodedData};</script>";

// Contexto URL (parâmetros)
echo urlencode($data);

// Contexto CSS
// Evite inserir dados de usuário em CSS. Se inevitável, sanitize forteme
