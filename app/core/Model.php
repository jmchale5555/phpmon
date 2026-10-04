<?php

namespace Model;

trait Model
{
    use \Model\Database;

    protected $limit        = 70;
    protected $offset       = 0;
    protected $order_type   = "desc";
    protected $order_column = "id";
    public $errors          = [];

    /** Reject anything that is not a bare SQL identifier. */
    private function identifier(string $name): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name))
        {
            throw new \InvalidArgumentException("Invalid SQL identifier: {$name}");
        }

        return $name;
    }

    /** Keep only declared columns. Writes must be explicit (no mass assignment). */
    private function writable(array $data): array
    {
        if (empty($this->allowedColumns))
        {
            throw new \LogicException(static::class . ' must define $allowedColumns before writing.');
        }

        return array_intersect_key($data, array_flip($this->allowedColumns));
    }

    /** Safe "order by ... limit ... offset ..." tail using typed values. */
    private function orderClause(): string
    {
        $column = $this->identifier((string) $this->order_column);
        $type   = strtolower((string) $this->order_type) === 'asc' ? 'asc' : 'desc';
        $limit  = max(0, (int) $this->limit);
        $offset = max(0, (int) $this->offset);

        return "order by $column $type limit $limit offset $offset";
    }

    public function all()
    {
        $query = "select * from $this->table " . $this->orderClause();

        return $this->query($query);
    }

    public function count(): int
    {
        $result = $this->query("select count(*) as total from $this->table");

        return $result ? (int) $result[0]->total : 0;
    }

    public function where(array $where_array = [], array $where_not_array = [], array $greater_than_array = []): array|bool
    {
        $clauses = [];
        $data    = [];

        $sets = [
            'w'  => ['operator' => '=',  'values' => $where_array],
            'nw' => ['operator' => '!=', 'values' => $where_not_array],
            'gt' => ['operator' => '>',  'values' => $greater_than_array],
        ];

        foreach ($sets as $prefix => $set)
        {
            foreach ($set['values'] as $key => $value)
            {
                $column = $this->identifier((string) $key);
                $param  = $prefix . '_' . $column;
                $clauses[] = "$column {$set['operator']} :$param";
                $data[$param] = $value;
            }
        }

        if (empty($clauses))
        {
            return [];
        }

        $query = "select * from $this->table where " . implode(' && ', $clauses) . ' ' . $this->orderClause();

        return $this->query($query, $data);
    }

    public function first($data, $data_not = [])
    {
        if (empty($data) && empty($data_not))
        {
            return false;
        }

        $clauses = [];
        $params  = [];

        $sets = [
            'w'  => ['operator' => '=',  'values' => (array) $data],
            'nw' => ['operator' => '!=', 'values' => (array) $data_not],
        ];

        foreach ($sets as $prefix => $set)
        {
            foreach ($set['values'] as $key => $value)
            {
                $column = $this->identifier((string) $key);
                $param  = $prefix . '_' . $column;
                $clauses[] = "$column {$set['operator']} :$param";
                $params[$param] = $value;
            }
        }

        $limit  = max(0, (int) $this->limit);
        $offset = max(0, (int) $this->offset);
        $query  = "select * from $this->table where " . implode(' && ', $clauses) . " limit $limit offset $offset";

        $result = $this->query($query, $params);
        if ($result)
        {
            return $result[0];
        }

        return false;
    }

    public function between($dataGreater = [], $dataLess = [])
    {
        if (empty($dataGreater) && empty($dataLess))
        {
            return false;
        }

        $clauses = [];
        $params  = [];

        $sets = [
            'bgt' => ['operator' => '>', 'values' => $dataGreater],
            'blt' => ['operator' => '<', 'values' => $dataLess],
        ];

        foreach ($sets as $prefix => $set)
        {
            foreach ($set['values'] as $key => $value)
            {
                $column = $this->identifier((string) $key);
                $param  = $prefix . '_' . $column;
                $clauses[] = "$column {$set['operator']} :$param";
                $params[$param] = $value;
            }
        }

        $limit  = max(0, (int) $this->limit);
        $offset = max(0, (int) $this->offset);
        $query  = "select * from $this->table where " . implode(' && ', $clauses) . " limit $limit offset $offset";

        $result = $this->query($query, $params);
        if ($result)
        {
            return $result;
        }

        return false;
    }

    public function insert($data)
    {
        $data = $this->writable($data);
        if (empty($data))
        {
            return false;
        }

        $columns = array_map([$this, 'identifier'], array_keys($data));
        $query   = "insert into $this->table (" . implode(',', $columns) . ") values (:" . implode(',:', $columns) . ")";

        return $this->execute($query, $data);
    }

    public function update($id, $data, $id_column = 'id'): bool
    {
        $data      = $this->writable($data);
        $id_column = $this->identifier((string) $id_column);

        if (empty($data))
        {
            return false;
        }

        $assignments = [];
        foreach (array_keys($data) as $key)
        {
            $column = $this->identifier((string) $key);
            $assignments[] = "$column = :$column";
        }

        $data[$id_column] = $id;
        $query = "update $this->table set " . implode(', ', $assignments) . " where $id_column = :$id_column";

        return $this->execute($query, $data);
    }

    public function delete($id, $id_column = 'id'): bool
    {
        $id_column = $this->identifier((string) $id_column);
        $query     = "delete from $this->table where $id_column = :$id_column";

        return $this->execute($query, [$id_column => $id]);
    }
}
