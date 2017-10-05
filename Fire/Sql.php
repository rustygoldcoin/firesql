<?php

namespace Fire;

use PDO;
use Fire\FireSqlException;
use Fire\Sql\Collection;
use Fire\Sql\Statement;

class Sql
{
    const TABLE_COLLECTION = 'collection';

    private $_pdo;

    private $_collections;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->_collections = [];
    }

    public function collection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($name, $this->_pdo);
        }

        return $this->_collections[$name];
    }

}
