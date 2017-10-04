<?php

namespace Fire\Sql;

class Statement {

    static private $_statements;

    static public function init()
    {
        self::$_statements = [
            'SQL_CREATE_COLLECTIONS_META_TABLE' =>
                'CREATE TABLE IF NOT EXISTS collections(' .
                    'name VARCHAR(50) NOT NULL' .
                ');'
        ];
    }

    static public function get($sqlStatement)
    {
        if (!is_array(self::$_statements)) {
            self::init();
        }
        return self::$_statements[$sqlStatement];
    }

}
