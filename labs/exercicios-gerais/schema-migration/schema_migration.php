<?php

class SchemaMigration
{
    public function __construct(
        private readonly PDO    $pdo,
        private readonly string $migrationsDir
    )
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name VARCHAR(255) UNIQUE,
                        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        );
    }

    public function migrate(): array
    {
        $migrated = [];

        foreach ($this->getPending() as $migration) {
            $sql = file_get_contents($this->migrationsDir . '/' . $migration);

            if ($sql === false) {
                throw new RuntimeException("Could not read migration: $migration");
            }

            $this->pdo->beginTransaction();

            try {
                $this->pdo->exec($sql);

                $stmt = $this->pdo->prepare(
                    'INSERT INTO migrations (name) VALUES (:name)'
                );

                $stmt->execute(['name' => $migration]);

                $this->pdo->commit();
                $migrated[] = $migration;
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }
        return $migrated;
    }

    public function getExecuted(): array
    {
        $stmt = $this->pdo->query('SELECT name FROM migrations ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPending(): array
    {
        $files = glob($this->migrationsDir . '/*.sql');

        if ($files === false) {
            return [];
        }

        $migrations = array_map('basename', $files);
        sort($migrations, SORT_STRING);

        $executed = $this->getExecuted();

        return array_values(array_diff($migrations, $executed));
    }
}
