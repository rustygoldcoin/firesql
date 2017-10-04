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

    private $_collectionsMeta;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->_collections = [];
        $this->_collectionsMeta = $this->_getCollectionsMeta();
    }

    public function collection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($this->_pdo);
        }

        return $this->_collections[$name];
    }

    private function _getCollectionsMeta()
    {
        $sql = Statement::get('SQL_CREATE_COLLECTIONS_META_TABLE');
        var_dump($sql);
    }

}
