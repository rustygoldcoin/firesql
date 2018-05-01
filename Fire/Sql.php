<?php

namespace Fire;

use \PDO;
use \Fire\Sql\Connector;
use \Fire\Sql\Collection;
use \Fire\Sql\Statement;

class Sql
{

    private $_collections;

    private $_connector;

    public function __construct(PDO $pdo)
    {
        $this->_connector = new Connector($pdo);
        $this->_collections = [];
        $createTables = Statement::get('CREATE_DB_TABLES');
        $this->_connector->exec($createTables);
    }

    public function collection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($name, $this->_connector);
        }

        return $this->_collections[$name];
    }

}
