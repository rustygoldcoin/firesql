<?php

namespace Fire\Sql;

class Statement {

    static private $_statements;

    static public function init()
    {
        self::$_statements = [
            'SQL_CREATE_COLLECTION_TABLE' =>
                'CREATE TABLE IF NOT EXISTS @collection (' .
                    'id TEXT NOT NULL,' .
                    'version INTEGER NOT NULL,' .
                    'created TEXT NOT NULL,' .
                    'obj BLOB NOT NULL' .
                ');',
            'SQL_INSERT_OBJECT_INTO_COLLECTION' =>
                'INSERT into @collection (id, version, created, obj) ' .
                'VALUES (@id, @revision, @created, @obj)'
        ];
    }

    static public function get($sqlStatement, $replaceVars = [])
    {
        if (!is_array(self::$_statements)) {
            self::init();
        }
        $sql = self::$_statements[$sqlStatement];
        foreach ($replaceVars as $find => $replace) {
            $sql = str_replace($find, $replace, $sql);
        }
        return $sql;
    }

}
