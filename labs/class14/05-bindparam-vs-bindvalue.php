<?php
// bindParam — vincula por REFERÊNCIA. A variável é lida no momento do execute().
$name = 'João';
$stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
$stmt->bindParam(':name', $name);
$stmt->bindValue(':email', 'joao@email.com');

$name = 'Maria'; // altera a referência
$stmt->execute(); // Maria é inserida! (bindParam usou o valor atualizado)
// email continua 'joao@email.com' (bindValue fixou o valor no momento da chamada)

// bindParam com tipo
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
$stmt->bindParam(':nulo', $nulo, PDO::PARAM_NULL);
