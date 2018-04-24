<?php

namespace Fire;

use PDO;
use Fire\FireSqlException;
use Fire\Sql\Collection;
use Fire\Sql\Statement;
use Fire\Bug;
use Fire\Bug\Panel\FireSqlPanel;
use Fire\Bug\SqlStatement;

class Sql
{
    private $_pdo;

    private $_collections;

    private $_firebug;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->_collections = [];
        $this->_firebug = Bug::get();

        $createTables = Statement::get('CREATE_DB_TABLES');
        $start = $this->_firebug->timer();
        $sqlStatement = new SqlStatement();
        $sqlStatement->setStatement($createTables);
        $this->_pdo->exec($createTables);
        $sqlStatement->setTime($this->_firebug->timer($start));
        $this->_firebug
            ->getPanel(FireSqlPanel::ID)
            ->addSqlStatement($sqlStatement);
    }

    public function collection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new Collection($name, $this->_pdo);
        }

        return $this->_collections[$name];
    }

}
