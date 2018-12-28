<?php

namespace Fire;

use \PDO;
use \Fire\Sql\Connector;
use \Fire\Sql\Collection;
use \Fire\Sql\Statement;

class Sql
{

    /**
     * Array of collections as cached objects.
     * @var array
     */
    private $_collections;

    /**
     * The connector class that stores the DB connection infomation.
     * @var \Fire\Sql\Connector
     */
    private $_connector;

    /**
     * The Constructor
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->_connector = new Connector($pdo);
        $this->_collections = [];
    }

    /**
     * Returns a collection object that will allow you to interact with the collection data.
     *
     * Default $options:
     * [
     *     'versionTracking' => false
     * ]
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function collection($name, $options = null)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($name, $this->_connector, $options);
        }

        return $this->_collections[$name];
    }

}
