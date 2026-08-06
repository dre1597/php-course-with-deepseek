<?php
$stmt = $pdo->prepare('SELECT id, name, email FROM users LIMIT 3');
$stmt->execute();

// FETCH_ASSOC — array associativo (RECOMENDADO)
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id']}: {$row['name']} — {$row['email']}<br>\n";
}

// FETCH_OBJ — objeto stdClass
while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "{$obj->id}: {$obj->name}<br>\n";
}

// FETCH_NUM — array indexado numericamente
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "{$row[0]}: {$row[1]}<br>\n";
}

// FETCH_BOTH — ambos (padrão, EVITE — duplica dados)
while ($row = $stmt->fetch(PDO::FETCH_BOTH)) {
    // $row[0] e $row['id'] apontam pro mesmo valor
}

// fetchAll — todas as linhas de uma vez
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "{$u['name']}<br>\n";
}

// fetchColumn — valor de uma única coluna
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
$stmt->execute();
$total = $stmt->fetchColumn();
echo "Total de usuários: {$total}<br>\n";
