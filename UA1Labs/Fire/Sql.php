<?php

/**
 *    __  _____   ___   __          __
 *   / / / /   | <  /  / /   ____ _/ /_  _____
 *  / / / / /| | / /  / /   / __ `/ __ `/ ___/
 * / /_/ / ___ |/ /  / /___/ /_/ / /_/ (__  )
 * `____/_/  |_/_/  /_____/`__,_/_.___/____/
 *
 * @package FireSql
 * @author UA1 Labs Developers https://ua1.us
 * @copyright Copyright (c) UA1 Labs
 */

namespace UA1Labs\Fire;

use \PDO;
use \UA1Labs\Fire\Sql\Connector;
use \UA1Labs\Fire\Sql\Collection;
use \UA1Labs\Fire\Di;

/**
 * The class responsible for being the entry point into connecting
 * to a database and obtaining a collection object to interact with
 * the database.
 */
class Sql
{

    /**
     * Array of collections as cached objects.
     * 
     * @var array<\UA1Labs\Fire\Sql\Collection>
     */
    private $collections;

    /**
     * The connector class that stores the DB connection infomation.
     * 
     * @var \UA1Labs\Fire\Sql\Connector
     */
    private $connector;

    /**
     * The class constructor.
     * 
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $fireDi = new Di();
        $this->connector = $fireDi->getWith(Connector::class, [$pdo]);
        // $this->connector = new Connector($pdo);
        $this->collections = [];
    }

    /**
     * Returns a collection object that will allow you to interact with the collection data.
     * Default $options:
     * [
     *     'versionTracking' => false
     * ]
     * 
     * @param string $name The name of the collection
     * @param array $options The collection options
     * @return \UA1Labs\Fire\Sql\Collection
     */
    public function collection($name, $options = null)
    {
        if (!isset($this->collections[$name])) {
            $fireDi = new Di();

            // $this->collections[$name] = new Collection($name, $this->connector, $options);
            $this->collections[$name] = $fireDi->getWith(Collection::class, [
                $name,
                $this->collector,
                $options
            ]);
        }

        return $this->collections[$name];
    }

}
