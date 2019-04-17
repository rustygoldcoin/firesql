<?php
/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireStudio
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

namespace Fire;

use \PDO;
use \Fire\Sql\Connector;
use \Fire\Sql\Collection;
use \Fire\Sql\Statement;

/**
 * The class responsible for being the entry point into connecting
 * to a database and obtaining a collection object.
 */
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
     * Default $options:
     * [
     *     'versionTracking' => false
     * ]
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
