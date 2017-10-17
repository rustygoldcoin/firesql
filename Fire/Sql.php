<?php

namespace Fire;

use PDO;
use Fire\FireSqlException;
use Fire\Sql\Collection;
use Fire\Sql\Statement;

class Sql
{
    private $_pdo;

    private $_collections;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->_collections = [];

        $createTables = Statement::get('CREATE_DB_TABLES');
        $this->_pdo->exec($createTables);
    }

    public function collection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($name, $this->_pdo);
        }

        return $this->_collections[$name];
    }

}
