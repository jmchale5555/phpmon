<?php

namespace Model;

use PDO;

trait Database
{
    /** Shared connection for the request. */
    private static ?PDO $connection = null;

    private function connect(): PDO
    {
        if (self::$connection === null)
        {
            $string = "mysql:host=" . DBHOST . ";port=" . DBPORT . ";dbname=" . DBNAME . ";charset=utf8mb4";
            self::$connection = new PDO($string, DBUSER, DBPASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ]);
        }

        return self::$connection;
    }

    /** Run a statement and return the affected/returned rows, or false when empty. */
    public function query($query, $data = [])
    {
        $stm = $this->connect()->prepare($query);

        if ($stm->execute($data))
        {
            $result = $stm->fetchAll();
            if (is_array($result) && count($result))
            {
                return $result;
            }
        }

        return false;
    }

    /** Run a write statement (insert/update/delete) and report success. */
    public function execute($query, $data = []): bool
    {
        return $this->connect()->prepare($query)->execute($data);
    }

    /** Last auto-increment id produced by this connection. */
    public function last_insert_id(): string
    {
        return $this->connect()->lastInsertId();
    }

    public function get_row($query, $data = [])
    {
        $stm = $this->connect()->prepare($query);

        if ($stm->execute($data))
        {
            $result = $stm->fetchAll();
            if (is_array($result) && count($result))
            {
                return $result[0];
            }
        }

        return false;
    }
}
