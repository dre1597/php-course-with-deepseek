<?php

class QueryBuilder
{
    private array $select = ['*'];
    private string $from = '';
    private array $where = [];
    private array $params = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private int $paramCounter = 0;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function from(string $table): self
    {
        $this->from = $table;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $paramName = 'p' . $this->paramCounter++;
        $this->where[] = ['AND', $column, $operator, $paramName];
        $this->params[$paramName] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = [];
        foreach ($values as $value) {
            $paramName = 'p' . $this->paramCounter++;
            $placeholders[] = ':' . $paramName;
            $this->params[$paramName] = $value;
        }
        $this->where[] = ['AND', $column, 'IN', '(' . implode(', ', $placeholders) . ')'];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }
        $this->orderBy[] = [$column, $direction];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    private function buildWhereClause(): string
    {
        if (empty($this->where)) {
            return '';
        }

        $conditions = [];
        foreach ($this->where as $i => [$logic, $column, $operator, $param]) {
            $logic = $i === 0 ? '' : $logic;
            if ($operator === 'IN') {
                $conditions[] = trim("{$logic} {$column} IN {$param}");
            } else {
                $conditions[] = trim("{$logic} {$column} {$operator} :{$param}");
            }
        }
        return ' WHERE ' . implode(' ', $conditions);
    }

    private function buildSelectSql(string $columns): string
    {
        $sql = "SELECT {$columns} FROM {$this->from}";
        $sql .= $this->buildWhereClause();

        if (!empty($this->orderBy)) {
            $orders = array_map(fn($o) => "{$o[0]} {$o[1]}", $this->orderBy);
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }

        if ($this->limit !== null || $this->offset !== null) {
            $limit = $this->limit ?? -1;
            $sql .= " LIMIT {$limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    public function get(): array
    {
        $columns = implode(', ', $this->select);
        $sql = $this->buildSelectSql($columns);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit = 1;
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function count(): int
    {
        $sql = $this->buildSelectSql('COUNT(*)');
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return (int) $stmt->fetchColumn();
    }

    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", array_keys($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data): int
    {
        $setClauses = [];
        $updateParams = [];
        foreach ($data as $column => $value) {
            $paramName = 'set_' . $column;
            $setClauses[] = "{$column} = :{$paramName}";
            $updateParams[$paramName] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $setClauses);
        $sql .= $this->buildWhereClause();

        $allParams = array_merge($updateParams, $this->params);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($allParams);

        return $stmt->rowCount();
    }

    public function delete(string $table): int
    {
        $sql = "DELETE FROM {$table}";
        $sql .= $this->buildWhereClause();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);

        return $stmt->rowCount();
    }
}
