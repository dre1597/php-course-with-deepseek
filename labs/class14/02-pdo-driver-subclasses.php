<?php
// PHP 8.4+: subclasses específicas de driver
$pdoMysql  = new Pdo\Mysql('host=localhost;dbname=app;charset=utf8mb4', 'root', '');
$pdoSqlite = new Pdo\Sqlite('sqlite:' . __DIR__ . '/app.sqlite');
$pdoPgsql  = new Pdo\Pgsql('host=localhost;dbname=app', 'postgres', 'password');

// Agora você pode tipar funções com o driver específico
function findUsersMySQL(Pdo\Mysql $pdo): array {
    return $pdo->query('SELECT * FROM users')->fetchAll();
}

// $pdoMysql é do tipo Pdo\Mysql (que herda de PDO)
findUsersMySQL($pdoMysql); // OK
// findUsersMySQL($pdoSqlite); // Erro de tipo
