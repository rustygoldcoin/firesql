<?php

namespace Fire\Sql;

use \PDO;
use \Fire\Bug;
use \Fire\Bug\Panel\FireSqlPanel;
use \Fire\Bug\SqlStatement;

class Connector
{

    private $_pdo;

    private $_firebug;

    public function __construct(PDO $pdo)
    {
        $this->_pdo = $pdo;
        $this->_firebug = Bug::get();
    }

    public function exec($sql) {
        //get start time of sql execution
        $start = $this->_firebug->timer();
        //execute sql
        $this->_pdo->exec($sql);
        //record sql statement
        if ($this->_firebug->isEnabled()) {
            $this->_recordSqlStatement($start, $sql);
        }
    }

    public function query($sql)
    {
        //get start time of sql execution
        $start = $this->_firebug->timer();
        //execute sql
        $records = $this->_pdo->query($sql);
        //record sql statement
        if ($this->_firebug->isEnabled()) {
            $this->_recordSqlStatement($start, $sql);
        }

        return $records;
    }

    public function quote($statement)
    {
        return $this->_pdo->quote($statement);
    }

    private function _recordSqlStatement($start, $sql)
    {
        $sqlStatement = new SqlStatement();
        $sqlStatement->setStatement($sql);
        $sqlStatement->setTime($this->_firebug->timer($start));
        $this->_firebug
            ->getPanel(FireSqlPanel::ID)
            ->addSqlStatement($sqlStatement);
    }

}