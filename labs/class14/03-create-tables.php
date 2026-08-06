<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// SQL para criar tabelas
$sql = "
    CREATE TABLE IF NOT EXISTS users (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        name       TEXT    NOT NULL,
        email      TEXT    NOT NULL UNIQUE,
        password      TEXT    NOT NULL,
        ativo      INTEGER DEFAULT 1,
        created_at  TEXT    DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titulo     TEXT    NOT NULL,
        conteudo   TEXT    NOT NULL,
        created_at  TEXT    DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
";

$pdo->exec($sql);
echo "Tabelas criadas com sucesso!<br>\n";
